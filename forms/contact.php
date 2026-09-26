<?php
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  header('Allow: POST');
  exit('Method Not Allowed');
}

$name = trim($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === false || $subject === '' || $message === '') {
  http_response_code(422);
  exit('Please complete all fields with a valid email address.');
}

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) {
  http_response_code(500);
  exit('Email could not be sent. PHPMailer is not installed.');
}

require $autoload;

$recipient = 'devloom62@gmail.com';
$smtpHost = getenv('SMTP_HOST');
$smtpUsername = getenv('SMTP_USERNAME');
$smtpPassword = getenv('SMTP_PASSWORD');
$fromAddress = getenv('SMTP_FROM') ?: $smtpUsername;
$smtpPort = (int) (getenv('SMTP_PORT') ?: 587);

if ($smtpHost === false || $smtpUsername === false || $smtpPassword === false || !$fromAddress) {
  http_response_code(500);
  exit('Email could not be sent. Configure SMTP settings before using the contact form.');
}

$mailer = new PHPMailer(true);

try {
  $mailer->isSMTP();
  $mailer->Host = $smtpHost;
  $mailer->SMTPAuth = true;
  $mailer->Username = $smtpUsername;
  $mailer->Password = $smtpPassword;
  $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mailer->Port = $smtpPort;
  $mailer->CharSet = 'UTF-8';

  $mailer->setFrom($fromAddress, 'DevLoom website');
  $mailer->addAddress($recipient);
  $mailer->addReplyTo($email, $name);
  $mailer->Subject = $subject;
  $mailer->Body = "Name: {$name}\nEmail: {$email}\n\n{$message}";
  $mailer->isHTML(false);
  $mailer->send();
} catch (Exception $exception) {
  http_response_code(500);
  exit('Email could not be sent. Check the SMTP configuration.');
}

echo 'OK';
?>
