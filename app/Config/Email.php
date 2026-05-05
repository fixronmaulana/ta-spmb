<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = 'fikronmaulana27@gmail.com';
    public string $fromName   = 'SPMB SMK Al-Munawwir IIBS';
    public string $recipients = '';

    public string $userAgent = 'CodeIgniter';

    // ✅ WAJIB SMTP
    public string $protocol = 'smtp';

    // ❌ tidak dipakai (biarkan default)
    public string $mailPath = '/usr/sbin/sendmail';

    // ✅ SMTP CONFIG (GMAIL)
    public string $SMTPHost = 'smtp.gmail.com';
    public string $SMTPUser = 'fikronmaulana27@gmail.com';
    public string $SMTPPass = 'ijtmgxdllzpckjfp'; // App Password
    public int    $SMTPPort = 587;
    public string $SMTPCrypto = 'tls';

    public string $SMTPAuthMethod = 'login';
    public int    $SMTPTimeout = 10;
    public bool   $SMTPKeepAlive = false;

    // ✅ FORMAT EMAIL
    public string $mailType = 'html';
    public string $charset  = 'UTF-8';

    public bool $wordWrap = true;
    public int  $wrapChars = 76;

    public bool $validate = true;
    public int  $priority = 3;

    public string $CRLF    = "\r\n";
    public string $newline = "\r\n";

    public bool $BCCBatchMode = false;
    public int  $BCCBatchSize = 200;

    public bool $DSN = false;
}