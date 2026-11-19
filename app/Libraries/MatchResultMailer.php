<?php

namespace App\Libraries;

use App\Models\ScoreboardMatchModel;

/**
 * Envoi des résultats d'un match officiel (feuille de match PDF + résumé) aux destinataires
 * cochés dans le formulaire admin du match.
 */
class MatchResultMailer
{
    /** @return string[] */
    public static function recipients(object $match): array
    {
        $emails = [];
        if ($match->send_to_club_ds) {
            $emails[] = ScoreboardMatchModel::CLUB_DS_EMAIL;
        }
        if ($match->send_to_dsr) {
            $emails[] = MatchSheetPdf::regionalDirector()['email'];
        }
        if ($match->send_to_cdr) {
            $emails[] = TeamRecapPdf::cdr()->email;
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
    public static function send(object $match): array
    {
        $recipients = self::recipients($match);
        if (!$recipients) {
            return [];
        }

        [$pdf, $filename] = MatchSheetPdf::render($match);

        $email = \Config\Services::email();
        // L'expéditeur doit rester le compte SMTP (send.one.com peut refuser un autre expéditeur) ; les réponses vont au DS.
        $email->setFrom(config('Email')->fromEmail, 'RBC Disonais');
        $email->setReplyTo(ScoreboardMatchModel::CLUB_DS_EMAIL, 'Directeur sportif — RBC Disonais');
        $email->setTo($recipients);
        $email->setSubject(self::subject($match));
        $email->setMessage(view('emails/match_result', [
            'match'  => $match,
            'signed' => (new \App\Models\ScoreboardSignatureModel())->isSigned((int) $match->id),
        ]));
        $email->attach($pdf, 'attachment', $filename, 'application/pdf');

        if (!$email->send(false)) {
            log_message('error', 'Envoi résultats match ' . $match->id . ' : ' . $email->printDebugger(['headers']));
            throw new \RuntimeException("L'envoi de l'e-mail a échoué.");
        }

        (new ScoreboardMatchModel())->update($match->id, [
            'result_sent_at' => ScoreboardMatchModel::now(),
            'result_sent_to' => mb_substr(implode(', ', $recipients), 0, 500),
        ]);

        return $recipients;
    }

    private static function subject(object $match): string
    {
        $competition = ScoreboardMatchModel::competitionLabel($match);
        return 'Résultat — ' . ScoreboardMatchModel::playerLabel($match, 'p1') . ' / ' . ScoreboardMatchModel::playerLabel($match, 'p2')
            . ' (' . ScoreboardMatchModel::disciplineLabel($match) . ($competition !== '' ? ' — ' . $competition : '') . ')';
    }
}
