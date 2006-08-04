'smtp_host' => null,
'smtp_port' => null,
'default_try_backends' => array('mail_smtp', 'mail_sendmail', 'mail'),
'sendmail_path' => null,
'sendmail_args' => null,
'no_iconv_domains' => array(
        //Theses domains report character set incorrectly (thus messing up the conversion)
        'yahoo.com',
    ),
