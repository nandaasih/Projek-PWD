<?php
// Default mailer config. Update with your SMTP provider credentials for reliable sending.
return [
    // If you want to use SMTP via PHPMailer, set 'use_smtp' => true and fill the settings below.
    'use_smtp' => false,
    'smtp' => [
        'host' => 'nda29930@gmail.com',
        'port' => 587,
        'username' => 'nanda',
        'password' => '111111',
        'secure' => 'tls' // 'tls' or 'ssl' or empty
    ],
    'from_email' => '2300018051@webmail.uad.ac.id',
    'from_name' => 'Tim Reservasi'
];
