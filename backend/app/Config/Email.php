<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public $fromEmail  = 'weletesadok@gmail.com';
    public $fromName   = 'Assesment App'; 
    public $recipients = '';

    public $protocol     = 'smtp';
    public $SMTPHost     = 'smtp.gmail.com';
    public $SMTPUser     = 'weletesadok@gmail.com';
    public $SMTPPass     = 'ewyz ojnl vgzq nlhu'; 
    public $SMTPPort     = 587; 
    public $SMTPTimeout  = 60;
    public $SMTPCrypto   = 'tls'; 

    public $mailType     = 'html';
    public $charset      = 'utf-8';
    public $wordWrap     = true;
    public $newline      = "\r\n";
    public $CRLF         = "\r\n";
    public $validate     = true;
    public $priority     = 3;
    public $BCCBatchMode = false;
    public $BCCBatchSize = 200;

    public $userAgent = 'CodeIgniter';

    public function __construct()
    {
        parent::__construct();
    }
}

