<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = '';
    public string $fromName   = '';
    public string $recipients = '';

    public string $userAgent = 'CodeIgniter';
    public string $protocol  = 'smtp';
    public string $mailPath  = '/usr/sbin/sendmail';

    public string $SMTPHost       = '';
    public string $SMTPUser       = '';
    public string $SMTPPass       = '';
    public int    $SMTPPort       = 587;
    public string $SMTPCrypto     = 'tls';
    public string $SMTPAuthMethod = 'login';
    public int    $SMTPTimeout    = 10;
    public bool   $SMTPKeepAlive  = false;

    public string $mailType  = 'html';
    public string $charset   = 'UTF-8';
    public bool   $wordWrap  = true;
    public int    $wrapChars = 76;
    public bool   $validate  = true;
    public int    $priority  = 3;
    public string $CRLF      = "\r\n";
    public string $newline   = "\r\n";
    public bool   $BCCBatchMode = false;
    public int    $BCCBatchSize = 200;
    public bool   $DSN       = false;

    public function __construct()
    {
        parent::__construct();

        // Baca semua credential dari .env — tidak ada yang hardcode di sini
        $this->fromEmail  = env('email.fromEmail', '');
        $this->fromName   = env('email.fromName',  'SPMB');
        $this->SMTPHost   = env('email.SMTPHost',   'smtp-relay.brevo.com');
        $this->SMTPUser   = env('email.SMTPUser',   '');
        $this->SMTPPass   = env('email.SMTPPass',   '');
        $this->SMTPPort   = (int) env('email.SMTPPort', 587);
        $this->SMTPCrypto = env('email.SMTPCrypto', 'tls');
        $this->mailType   = env('email.mailType',   'html');
        $this->charset    = env('email.charset',    'UTF-8');
    }
}