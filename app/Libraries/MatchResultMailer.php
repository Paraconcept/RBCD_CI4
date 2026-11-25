<?php

namespace App\Libraries;

use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardSignatureModel;
use App\Models\ScoreboardTeamEncounterModel;
use App\Models\SiteSettingModel;

/**
 * Envoi des résultats d'un match officiel (feuille de match PDF + résumé) aux destinataires
 * cochés dans le formulaire admin du match, joueurs compris.
 *
 * Envoi automatique : à la fin du match, ou à la signature quand elle est demandée. Une feuille à signer
 * qui ne l'est pas part quand même, sans signature, AUTO_DELAY minutes après la fin du match.
 */
class MatchResultMailer
{
    public const AUTO_DELAY = 15;
    /** Secondes laissées à l'arbitre pour corriger (touche « − » sur l'écran de résultat) avant l'envoi. */
    public const GRACE = 60;

    /** @return string[] */
    public static function recipients(object $match): array
    {
        $emails = [];
        foreach ([1, 2] as $n) {
            if (!empty($match->{'send_to_p' . $n}) && !((int) ($match->absent ?? 0) & $n)) {
                $emails[] = $match->{'p' . $n . '_email'} ?? null;
            }
        }
        if ($match->send_to_club_ds) {
            $emails[] = $match->club_ds_email ?: ScoreboardMatchModel::CLUB_DS_EMAIL;
        }
        if ($match->send_to_dsr) {
            $emails[] = $match->dsr_email ?: MatchSheetPdf::regionalDirector()['email'];
        }
        if ($match->send_to_cdr) {
            $emails[] = $match->cdr_email ?: TeamRecapPdf::cdr()->email;
        }
        if ($match->send_to_organizer_ds) {
            $emails[] = $match->organizer_ds_email;
        }
        $emails = array_filter(array_map(static fn ($e) => strtolower(trim((string) $e)), $emails),
            static fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        return array_values(array_unique($emails));
    }

    /**
     * @return string[] destinataires, vide si personne n'est coché
     * @throws \RuntimeException si l'envoi échoue
     */
    public static function send(object $match, bool $corrected = false): array
    {
        $recipients = self::recipients($match);
        if (!$recipients) {
            return [];
        }

        $model = new ScoreboardMatchModel();
        try {
            [$pdf, $filename] = MatchSheetPdf::render($match);

            $email = \Config\Services::email();
            // L'expéditeur doit rester le compte SMTP (send.one.com peut refuser un autre expéditeur) ; les réponses vont au DS.
            $email->setFrom(config('Email')->fromEmail, 'RBC Disonais');
            $email->setReplyTo(ScoreboardMatchModel::CLUB_DS_EMAIL, 'Directeur sportif — RBC Disonais');
            $email->setTo($recipients);
            $email->setSubject(($corrected ? 'Résultat corrigé — ' : 'Résultat — ') . self::subject($match));
            $email->setMessage(view('emails/match_result', [
                'match'  => $match,
                'signed' => (new ScoreboardSignatureModel())->isSigned((int) $match->id),
            ]));
            $email->attach($pdf, 'attachment', $filename, 'application/pdf');

            if (!$email->send(false)) {
                log_message('error', 'Envoi résultats match ' . $match->id . ' : ' . $email->printDebugger(['headers']));
                throw new \RuntimeException("L'envoi de l'e-mail a échoué.");
            }
        } catch (\Throwable $e) {
            $model->update($match->id, ['result_send_error' => mb_substr(date('d/m/Y H:i') . ' — ' . $e->getMessage(), 0, 255)]);
            throw $e instanceof \RuntimeException ? $e : new \RuntimeException($e->getMessage(), 0, $e);
        }

        $model->update($match->id, [
            'result_sent_at'    => ScoreboardMatchModel::now(),
            'result_sent_to'    => mb_substr(implode(', ', $recipients), 0, 500),
            'result_send_error' => null,
        ]);

        return $recipients;
    }

    /**
     * Envoi automatique (fin du match, signature, délai écoulé) : adresses des joueurs complétées, erreur gardée
     * sur le match plutôt que levée, puis feuille récapitulative si c'était le dernier match d'une rencontre d'équipe.
     *
     * @return array{sent: string[], error: ?string}
     */
    public static function deliver(object $match): array
    {
        $out = ['sent' => [], 'error' => null];
        if (!self::concerned($match)) {
            return $out;
        }

        $match = self::withPlayerEmails($match);
        try {
            // Adresses déjà servies une fois puis match rouvert (correction après la fin) : l'e-mail le dit
            $out['sent'] = self::send($match, $match->result_sent_at === null && !empty($match->result_sent_to));
        } catch (\Throwable $e) {
            log_message('error', 'Résultats du match ' . $match->id . ' non envoyés : ' . $e->getMessage());
            $out['error'] = $e->getMessage();
        }

        if ($match->team_encounter_id) {
            $encounter = (new ScoreboardTeamEncounterModel())->find((int) $match->team_encounter_id);
            if ($encounter) {
                TeamRecapMailer::deliver($encounter);
            }
        }

        return $out;
    }

    /** Matchs dont l'envoi automatique est arrivé à échéance (signature attendue en vain) : quelques-uns par appel. */
    public static function sendPending(int $limit = 3): void
    {
        $model = new ScoreboardMatchModel();
        $since = self::autoSince();
        foreach ($model->where('type', 'official')->where('status', 'finished')->where('absent', 0)
                     ->where('result_sent_at IS NULL')->where('result_send_error IS NULL')
                     ->where("COALESCE(p3_last_name, '') = ''", null, false)
                     ->where('finished_at >=', $since)
                     ->where('finished_at <=', date('Y-m-d H:i:s', time() - self::GRACE))
                     ->orderBy('finished_at', 'ASC')->findAll(20) as $match) {
            if ($limit > 0 && self::due($match)) {
                self::deliver($match);
                $limit--;
            }
        }
        TeamRecapMailer::sendPending();
    }

    /** Envoi automatique à faire maintenant pour ce match (fin sans signature demandée, ou délai de signature écoulé). */
    public static function due(object $match): bool
    {
        if (!self::concerned($match) || $match->result_sent_at || $match->result_send_error
            || (string) $match->finished_at < self::autoSince()) {
            return false;
        }
        $finished = strtotime((string) $match->finished_at);
        if (!$match->signature_required) {
            return $finished <= time() - self::GRACE;
        }
        return $finished <= time() - self::AUTO_DELAY * 60;
    }

    /**
     * État de l'envoi pour l'écran de résultat et la page du billard.
     * @return array{state: string, at: ?string, to: string[], error: ?string, due: ?string}
     *         state : sent, error, waiting (signature attendue, envoi à « due »), sending, none (personne à qui l'envoyer)
     */
    public static function status(object $match): array
    {
        $out = ['state' => 'none', 'at' => null, 'to' => [], 'error' => null, 'due' => null];
        if ($match->result_sent_at) {
            return ['state' => 'sent', 'at' => date('H:i', strtotime($match->result_sent_at)),
                    'to' => self::labels($match, explode(', ', (string) $match->result_sent_to))] + $out;
        }
        if ($match->result_send_error) {
            return ['state' => 'error', 'error' => $match->result_send_error] + $out;
        }
        if (!self::concerned($match) || (string) $match->finished_at < self::autoSince()
            || (!self::recipients($match) && !self::playerToLookUp($match))) {
            return $out;
        }
        if ($match->signature_required && !(new ScoreboardSignatureModel())->isSigned((int) $match->id)) {
            $due = strtotime((string) $match->finished_at) + self::AUTO_DELAY * 60;
            return ['state' => $due > time() ? 'waiting' : 'sending', 'due' => date('H:i', $due)] + $out;
        }
        return ['state' => 'sending'] + $out;
    }

    /** Adresses vides des joueurs cochés : recherchées (club, marquoir, fédération) et retenues sur le match. */
    public static function withPlayerEmails(object $match): object
    {
        $update = [];
        foreach ([1, 2] as $n) {
            $p = 'p' . $n;
            if (empty($match->{'send_to_' . $p}) || ((int) $match->absent & $n) || trim((string) ($match->{$p . '_email'} ?? '')) !== '') {
                continue;
            }
            $found = PlayerEmailLookup::find($match->{$p . '_license'}, $match->{$p . '_member_id'} ? (int) $match->{$p . '_member_id'} : null,
                                             $match->{$p . '_last_name'}, $match->{$p . '_first_name'});
            if ($found) {
                $update[$p . '_email'] = $match->{$p . '_email'} = $found['email'];
            }
        }
        if ($update) {
            (new ScoreboardMatchModel())->update($match->id, $update);
        }
        return $match;
    }

    public static function autoSince(): string
    {
        static $since = null;
        return $since ??= (string) (new SiteSettingModel())->getSetting('scoreboard_auto_send_since', date('Y-m-d H:i:s'));
    }

    private static function concerned(object $match): bool
    {
        return $match->type === 'official' && $match->status === 'finished' && !(int) $match->absent && !ScoreboardMatchModel::isTrio($match);
    }

    private static function playerToLookUp(object $match): bool
    {
        foreach ([1, 2] as $n) {
            if (!empty($match->{'send_to_p' . $n}) && trim((string) ($match->{'p' . $n . '_email'} ?? '')) === '') {
                return true;
            }
        }
        return false;
    }

    /** Destinataires présentés par leur rôle (nom du joueur, DS…) : les adresses ne s'affichent pas au marquoir. */
    private static function labels(object $match, array $emails): array
    {
        $roles = [];
        foreach ([1, 2] as $n) {
            if ($e = strtolower(trim((string) ($match->{'p' . $n . '_email'} ?? '')))) {
                $roles[$e] = ScoreboardMatchModel::playerLabel($match, 'p' . $n);
            }
        }
        $roles[strtolower((string) ($match->club_ds_email ?: ScoreboardMatchModel::CLUB_DS_EMAIL))] ??= 'DS du RBCD';
        if ($match->dsr_email ?: MatchSheetPdf::regionalDirector()['email']) {
            $roles[strtolower((string) ($match->dsr_email ?: MatchSheetPdf::regionalDirector()['email']))] ??= 'DS régional';
        }
        if ($match->cdr_email ?: TeamRecapPdf::cdr()->email) {
            $roles[strtolower((string) ($match->cdr_email ?: TeamRecapPdf::cdr()->email))] ??= 'responsable CDR';
        }
        if ($match->organizer_ds_email) {
            $roles[strtolower($match->organizer_ds_email)] ??= 'DS du club organisateur';
        }

        return array_values(array_unique(array_map(static fn ($e) => $roles[strtolower(trim($e))] ?? 'autre destinataire', array_filter($emails))));
    }

    private static function subject(object $match): string
    {
        $competition = ScoreboardMatchModel::competitionLabel($match);
        return ScoreboardMatchModel::playerLabel($match, 'p1') . ' / ' . ScoreboardMatchModel::playerLabel($match, 'p2')
            . ' (' . ScoreboardMatchModel::disciplineLabel($match) . ($competition !== '' ? ' — ' . $competition : '') . ')';
    }
}
