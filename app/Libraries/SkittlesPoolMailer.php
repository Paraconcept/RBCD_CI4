<?php

namespace App\Libraries;

use App\Models\ScoreboardMatchModel;
use App\Models\ScoreboardSkittlesPoolModel;
use App\Models\ScoreboardSkittlesSignatureModel;

/**
 * Envoi de la feuille récapitulative d'une Soirée Poule 5 Quilles (seule feuille envoyée : pas de feuille de match)
 * au responsable régional du 5 quilles et au DS du club.
 *
 * Automatique dès que les trois matchs sont terminés et la feuille signée par les trois joueurs (si demandé) ;
 * sans signature, elle part MatchResultMailer::AUTO_DELAY minutes après la fin du dernier match.
 */
class SkittlesPoolMailer
{
    /** @return string[] */
    public static function recipients(object $pool): array
    {
        $emails = [
            $pool->send_to_regional ? ($pool->regional_email ?: ScoreboardSkittlesPoolModel::regional()['email']) : null,
            $pool->send_to_club_ds ? ($pool->club_ds_email ?: ScoreboardMatchModel::CLUB_DS_EMAIL) : null,
        ];
        $emails = array_filter(array_map(static fn ($e) => strtolower(trim((string) $e)), $emails),
            static fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

        return array_values(array_unique($emails));
    }

    /**
     * @return string[] destinataires
     * @throws \RuntimeException poule incomplète, aucun destinataire ou envoi en échec
     */
    public static function send(object $pool): array
    {
        $model = new ScoreboardSkittlesPoolModel();
        $sheet = $model->sheet($pool);
        if (!$sheet['complete']) {
            throw new \RuntimeException('La feuille récapitulative ne peut être envoyée que lorsque les trois matchs sont terminés.');
        }
        $recipients = self::recipients($pool);
        if (!$recipients) {
            throw new \RuntimeException('Aucune adresse e-mail valide pour envoyer la feuille récapitulative.');
        }

        try {
            [$pdf, $filename] = SkittlesPoolPdf::render($pool);

            $email = \Config\Services::email(null, false); // instance neuve : plusieurs envois peuvent se suivre dans la même requête
            // L'expéditeur doit rester le compte SMTP (send.one.com peut refuser un autre expéditeur) ; les réponses vont au DS.
            $email->setFrom(config('Email')->fromEmail, 'RBC Disonais');
            $email->setReplyTo(ScoreboardMatchModel::CLUB_DS_EMAIL, 'Directeur sportif — RBC Disonais');
            $email->setTo($recipients);
            $email->setSubject('Feuille récapitulative — Tournoi régional 5 Quilles — ' . $pool->name
                . ($pool->scheduled_at ? ' (' . date('d/m/Y', strtotime($pool->scheduled_at)) . ')' : ''));
            $email->setMessage(view('emails/skittles_pool', [
                'pool'   => $pool,
                'sheet'  => $sheet,
                'signed' => (new ScoreboardSkittlesSignatureModel())->isSigned((int) $pool->id),
            ]));
            $email->attach($pdf, 'attachment', $filename, 'application/pdf');

            if (!$email->send(false)) {
                log_message('error', 'Envoi feuille poule 5 quilles ' . $pool->id . ' : ' . $email->printDebugger(['headers']));
                throw new \RuntimeException("L'envoi de l'e-mail a échoué.");
            }
        } catch (\Throwable $e) {
            $model->update($pool->id, ['send_error' => mb_substr(date('d/m/Y H:i') . ' — ' . $e->getMessage(), 0, 255)]);
            throw $e instanceof \RuntimeException ? $e : new \RuntimeException($e->getMessage(), 0, $e);
        }

        $model->update($pool->id, ['sent_at' => ScoreboardMatchModel::now(), 'sent_to' => mb_substr(implode(', ', $recipients), 0, 500), 'send_error' => null]);

        return $recipients;
    }

    /** Envoi automatique si la poule est prête et pas encore envoyée ; l'erreur reste sur la poule. */
    public static function deliver(object $pool): void
    {
        if (!self::due($pool)) {
            return;
        }
        try {
            self::send($pool);
        } catch (\Throwable $e) {
            log_message('error', 'Feuille poule 5 quilles ' . $pool->id . ' non envoyée : ' . $e->getMessage());
        }
    }

    public static function sendPending(int $limit = 2): void
    {
        $rows = (new ScoreboardSkittlesPoolModel())->where('sent_at IS NULL')->where('send_error IS NULL')
            ->where('scheduled_at >=', date('Y-m-d H:i:s', strtotime(MatchResultMailer::autoSince()) - 86400))
            ->orderBy('scheduled_at', 'ASC')->findAll(20);
        foreach ($rows as $pool) {
            if ($limit > 0 && self::due($pool)) {
                self::deliver($pool);
                $limit--;
            }
        }
    }

    public static function due(object $pool): bool
    {
        return !$pool->sent_at && !$pool->send_error && self::progress($pool)['state'] === 'sending';
    }

    /**
     * Où en est la feuille récapitulative (même forme que TeamRecapMailer::progress pour l'écran de résultat).
     * @return array{state: string, at: ?string, error: ?string, done: int, total: int, due: ?string}
     */
    public static function progress(object $pool): array
    {
        $out = ['state' => 'none', 'at' => null, 'error' => null, 'done' => 0, 'total' => count(ScoreboardSkittlesPoolModel::PAIRS), 'due' => null];
        if ($pool->sent_at) {
            return ['state' => 'sent', 'at' => date('H:i', strtotime($pool->sent_at))] + $out;
        }
        if ($pool->send_error) {
            return ['state' => 'error', 'error' => $pool->send_error] + $out;
        }

        $matches = (new ScoreboardSkittlesPoolModel())->getMatches((int) $pool->id);
        $done    = array_filter($matches, static fn ($m) => $m->status === 'finished');
        $out['done'] = count($done);
        if (!self::recipients($pool)) {
            return $out;
        }
        if (count($done) < $out['total']) {
            return ['state' => 'matches'] + $out;
        }

        $last = max(array_map(static fn ($m) => strtotime((string) ($m->finished_at ?: $m->updated_at)), $done));
        if (date('Y-m-d H:i:s', $last) < MatchResultMailer::autoSince()) {
            return $out;
        }
        $unsigned = $pool->signature_required && !(new ScoreboardSkittlesSignatureModel())->isSigned((int) $pool->id);
        $due      = $last + MatchResultMailer::AUTO_DELAY * 60;
        if ($unsigned && $due > ScoreboardMatchModel::nowTs()) {
            return ['state' => 'waiting', 'due' => date('H:i', $due)] + $out;
        }

        return ['state' => 'sending'] + $out;
    }
}
