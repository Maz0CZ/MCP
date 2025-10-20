<?php
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;

function create_mailer(): PHPMailer
{
    if (!class_exists(PHPMailer::class)) {
        $autoloader = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($autoloader)) {
            require_once $autoloader;
        }
    }

    $config = require __DIR__ . '/config.php';
    $mail = new PHPMailer(true);
    if ($config['mail']['enabled']) {
        $mail->isSMTP();
        $mail->Host = $config['mail']['host'];
        $mail->Port = $config['mail']['port'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['mail']['username'];
        $mail->Password = $config['mail']['password'];
        $mail->SMTPSecure = $config['mail']['encryption'];
    }
    $mail->setFrom($config['mail']['from_email'], $config['mail']['from_name']);
    return $mail;
}
