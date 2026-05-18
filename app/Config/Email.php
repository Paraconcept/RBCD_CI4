<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = 'noreply@rbcdisonais.be';
    public string $fromName   = 'RBC Disonais';
    public string $recipients = '';

    /**
     * The "user agent"
     */
    public string $userAgent = 'CodeIgniter';

    /**
     * The mail sending protocol: mail, sendmail, smtp
     */
    public string $protocol = 'smtp';

    /**
     * The server path to Sendmail.
     */
    public string $mailPath = '/usr/sbin/sendmail';

    // ── DEV : Mailtrap (intercepte tous les emails sans les envoyer)
    // ── PROD : commenter le bloc Mailtrap et décommenter le bloc One.com
    public string $SMTPHost   = 'sandbox.smtp.mailtrap.io';
    public string $SMTPUser   = 'bd395758f1e393';
    public string $SMTPPass   = '51b9255f24759e';
    public int    $SMTPPort   = 2525;
    public string $SMTPCrypto = 'tls';

    // ── PROD One.com (décommenter au moment de la mise en ligne)
    // public string $SMTPHost   = 'send.one.com';
    // public string $SMTPUser   = 'noreply@rbcdisonais.be';
    // public string $SMTPPass   = 'MotDePasseEmailOnecom';
    // public int    $SMTPPort   = 587;
    // public string $SMTPCrypto = 'tls';

    /**
     * Which SMTP authentication method to use: login, plain
     */
    public string $SMTPAuthMethod = 'login';

    public int  $SMTPTimeout    = 30;
    public bool $SMTPKeepAlive  = false;

    /**
     * Enable word-wrap
     */
    public bool $wordWrap = true;

    /**
     * Character count to wrap at
     */
    public int $wrapChars = 76;

    /**
     * Type of mail, either 'text' or 'html'
     */
    public string $mailType = 'html';

    /**
     * Character set (utf-8, iso-8859-1, etc.)
     */
    public string $charset = 'UTF-8';

    /**
     * Whether to validate the email address
     */
    public bool $validate = false;

    /**
     * Email Priority. 1 = highest. 5 = lowest. 3 = normal
     */
    public int $priority = 3;

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $CRLF = "\r\n";

    /**
     * Newline character. (Use “\r\n” to comply with RFC 822)
     */
    public string $newline = "\r\n";

    /**
     * Enable BCC Batch Mode.
     */
    public bool $BCCBatchMode = false;

    /**
     * Number of emails in each BCC batch
     */
    public int $BCCBatchSize = 200;

    /**
     * Enable notify message from server
     */
    public bool $DSN = false;
}
