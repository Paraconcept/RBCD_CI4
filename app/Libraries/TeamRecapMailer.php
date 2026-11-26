<?php

namespace App\Libraries;

use App\Models\ScoreboardTeamEncounterModel;
use App\Models\ScoreboardTeamSignatureModel;

/**
 * Feuille récapitulative d'une rencontre d'équipe (+ feuilles de match) envoyée aux deux clubs, au responsable CDR
 * et aux joueurs des matchs (chacun décochable).
 *
 * Envoi automatique dès que la rencontre est complète : tous les matchs terminés, signés là où la signature
 * est demandée, et feuille récapitulative signée par les capitaines si demandé. Ce qui n'est pas signé
 * MatchResultMailer::AUTO_DELAY minutes après la fin du dernier match part quand même.
 */
class TeamRecapMailer
{
    /**
     * Destinataires cochés : clubs et responsable CDR, puis les joueurs des matchs (absents exceptés).
     * $lookup : adresse d'un joueur encore inconnue recherchée (membre, marquoir, site fédéral) — seulement à l'envoi.
     * @return string[]
     */
    public static function recipients(object $encounter, bool $lookup = false): array
    {
        return array_values(array_unique(array_merge(self::clubRecipients($encounter), self::playerRecipients($encounter, $lookup))));
    }

    /** @return string[] */
    public static function clubRecipients(object $encounter): array
    {
        $emails = [
            ($encounter->recap_to_team ?? 1) ? $encounter->team_email : null,
            ($encounter->recap_to_opponent ?? 1) ? $encounter->opponent_email : null,
            ($encounter->recap_to_cdr ?? 1) ? TeamRecapPdf::cdr()->email : null,
        ];
        return self::clean($emails);
    }

    /** @return string[] */
    public static function playerRecipients(object $encounter, bool $lookup = false): array
    {
        $emails = [];
        foreach ((new ScoreboardTeamEncounterModel())->getMatches((int) $encounter->id) as $m) {
            if ($lookup) {
                $m = MatchResultMailer::withPlayerEmails($m, 'recap_to_');
            }
            foreach ([1, 2] as $n) {
                if (!empty($m->{'recap_to_p' . $n}) && !((int) $m->absent & $n)) {
                    $emails[] = $m->{'p' . $n . '_email'};
                }
            }
        }
        return self::clean($emails);
    }

    private static function clean(array $emails): array
    {
        $emails = array_filter(array_map(static fn ($e) => strtolower(trim((string) $e)), $emails),
            static fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        return array_values(array_unique($emails));
    }

    /**
     * @return array{recipients: string[], sheets: int}
     * @throws \RuntimeException rencontre incomplète, aucun destinataire ou envoi en échec
     */
    public static function send(object $encounter): array
    {
        $model   = new ScoreboardTeamEncounterModel();
        $matches = $model->getMatches((int) $encounter->id);
        $result  = ScoreboardTeamEncounterModel::computeResult($encounter, $matches);
        if (!$result['complete']) {
            throw new \RuntimeException('La feuille récapitulative ne peut être envoyée que lorsque tous les matchs sont terminés.');
        }
        $clubs   = self::clubRecipients($encounter);
        $players = array_values(array_diff(self::playerRecipients($encounter, true), $clubs));
        $recipients = array_merge($clubs, $players);
        if (!$recipients) {
            throw new \RuntimeException('Aucune adresse e-mail valide pour envoyer la feuille récapitulative.');
        }

        try {
            [$pdf, $filename] = TeamRecapPdf::render($encounter);
            $sheets = array_values(array_map(static fn ($m) => MatchSheetPdf::render($m),
                array_filter($matches, static fn ($m) => $m->status === 'finished')));

            $email = \Config\Services::email(null, false); // instance neuve : plusieurs envois peuvent se suivre dans la même requête
            // L'expéditeur doit rester le compte SMTP (send.one.com peut refuser un autre expéditeur) ; les réponses vont au DS.
            $email->setFrom(config('Email')->fromEmail, 'RBC Disonais');
            $email->setReplyTo('ds@rbcd.be', 'Directeur sportif — RBC Disonais');
            // Joueurs en copie cachée : leurs adresses ne circulent pas entre eux ni vers l'autre club
            $email->setTo($clubs ?: [config('Email')->fromEmail]);
            if ($players) {
                $email->setBCC($players);
            }
            $email->setSubject('Feuille récapitulative — ' . ScoreboardTeamEncounterModel::homeLabel($encounter)
                . ' / ' . ScoreboardTeamEncounterModel::awayLabel($encounter) . ' (' . $encounter->code . ')');
            $email->setMessage(view('emails/team_recap', ['encounter' => $encounter, 'result' => $result]));
            $email->attach($pdf, 'attachment', $filename, 'application/pdf');
            foreach ($sheets as $i => [$sheet, $sheetName]) {
                // Préfixe d'ordre : deux matchs peuvent opposer les mêmes noms et l'ordre des pièces jointes suit la feuille
                $email->attach($sheet, 'attachment', sprintf('%d_%s', $i + 1, $sheetName), 'application/pdf');
            }

            if (!$email->send(false)) {
                log_message('error', 'Envoi feuille récapitulative ' . $encounter->code . ' : ' . $email->printDebugger(['headers']));
                throw new \RuntimeException("L'envoi de l'e-mail a échoué.");
            }
        } catch (\Throwable $e) {
            $model->update($encounter->id, ['recap_send_error' => mb_substr(date('d/m/Y H:i') . ' — ' . $e->getMessage(), 0, 255)]);
            throw $e instanceof \RuntimeException ? $e : new \RuntimeException($e->getMessage(), 0, $e);
        }

        $model->update($encounter->id, ['recap_sent_at' => date('Y-m-d H:i:s'), 'recap_sent_to' => implode(', ', $recipients), 'recap_send_error' => null]);

        return ['recipients' => $recipients, 'sheets' => count($sheets)];
    }

    /** Envoi automatique si la rencontre est prête et pas encore envoyée ; l'erreur reste sur la rencontre. */
    public static function deliver(object $encounter): void
    {
        if (!self::due($encounter)) {
            return;
        }
        try {
            self::send($encounter);
        } catch (\Throwable $e) {
            log_message('error', 'Feuille récapitulative ' . $encounter->code . ' non envoyée : ' . $e->getMessage());
        }
    }

    public static function sendPending(int $limit = 2): void
    {
        $model = new ScoreboardTeamEncounterModel();
        $rows  = $model->where('recap_sent_at IS NULL')->where('recap_send_error IS NULL')
                       ->where('scheduled_at >=', date('Y-m-d H:i:s', strtotime(MatchResultMailer::autoSince()) - 86400))
                       ->orderBy('scheduled_at', 'ASC')->findAll(20);
        foreach ($rows as $encounter) {
            if ($limit > 0 && self::due($encounter)) {
                self::deliver($encounter);
                $limit--;
            }
        }
    }

    public static function due(object $encounter): bool
    {
        if ($encounter->recap_sent_at || $encounter->recap_send_error || !self::recipients($encounter)) {
            return false;
        }
        $state = self::progress($encounter);
        return $state['state'] === 'sending';
    }

    /**
     * Où en est la feuille récapitulative.
     * @return array{state: string, at: ?string, error: ?string, done: int, total: int, due: ?string}
     *         state : sent, error, matches (matchs encore à jouer), waiting (signatures attendues jusqu'à « due »), sending, none
     */
    public static function progress(object $encounter): array
    {
        $out = ['state' => 'none', 'at' => null, 'error' => null, 'done' => 0, 'total' => 0, 'due' => null];
        if ($encounter->recap_sent_at) {
            return ['state' => 'sent', 'at' => date('H:i', strtotime($encounter->recap_sent_at))] + $out;
        }
        if ($encounter->recap_send_error) {
            return ['state' => 'error', 'error' => $encounter->recap_send_error] + $out;
        }

        $closing = (new ScoreboardTeamEncounterModel())->closingState($encounter);
        $total   = count($closing['matches']);
        $done    = count(array_filter($closing['matches'], static fn ($i) => in_array($i['state'], ['forfeit', 'signed', 'done', 'to_sign'], true)));
        $out     = ['done' => $done, 'total' => $total] + $out;
        if (!$total || !self::recipients($encounter)) {
            return $out;
        }
        if ($done < $total) {
            return ['state' => 'matches'] + $out;
        }

        $last = max(array_map(static fn ($i) => strtotime((string) ($i['match']->finished_at ?: $i['match']->updated_at)), $closing['matches']));
        if (date('Y-m-d H:i:s', $last) < MatchResultMailer::autoSince()) {
            return $out;
        }
        $unsigned = !$closing['ready']
            || ($encounter->recap_signature_required && !(new ScoreboardTeamSignatureModel())->isSigned((int) $encounter->id));
        $due = $last + MatchResultMailer::AUTO_DELAY * 60;
        if ($unsigned && $due > time()) {
            return ['state' => 'waiting', 'due' => date('H:i', $due)] + $out;
        }

        return ['state' => 'sending'] + $out;
    }
}
