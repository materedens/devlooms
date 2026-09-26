<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

putenv('SMTP_HOST=smtp.gmail.com');
putenv('SMTP_PORT=587');
putenv('SMTP_ENCRYPTION=tls');
putenv('SMTP_USERNAME=devloom62@gmail.com');
putenv('SMTP_PASSWORD=zpxvuyvdykmrtlyf');
putenv('SMTP_FROM_EMAIL=devloom62@gmail.com');
putenv('SMTP_FROM_NAME=DevLoom Website');
putenv('DEVLOOMS_TO_EMAIL=devloom62@gmail.com');

require __DIR__ . '/../vendor/autoload.php';

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

$recipient = getenv('DEVLOOMS_TO_EMAIL') ?: 'devloom62@gmail.com';
$body = "Name: {$name}\nEmail: {$email}\n\n{$message}";

$smtpHost = getenv('SMTP_HOST');
if (!$smtpHost) {
  http_response_code(503);
  exit('Email is not configured on this server. Add SMTP_HOST, SMTP_USERNAME, SMTP_PASSWORD, and SMTP_FROM_EMAIL in the environment before sending messages.');
}

$mail = new PHPMailer(true);

try {
  $mail->isSMTP();
  $mail->Host = $smtpHost;
  $mail->SMTPAuth = true;
  $mail->Username = getenv('SMTP_USERNAME') ?: '';
  $mail->Password = getenv('SMTP_PASSWORD') ?: '';

  $encryption = getenv('SMTP_ENCRYPTION');
  if ($encryption === 'tls') {
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  } elseif ($encryption === 'ssl') {
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  }

  $mail->Port = (int) (getenv('SMTP_PORT') ?: 587);

  $mail->CharSet = 'UTF-8';
  $mail->setFrom(getenv('SMTP_FROM_EMAIL') ?: $recipient, getenv('SMTP_FROM_NAME') ?: 'DevLoom Website');
  $mail->addAddress($recipient, 'DevLoom');
  $mail->addReplyTo($email, $name);
  $mail->Subject = $subject;
  $mail->Body = $body;
  $mail->AltBody = $body;

  if (!$mail->send()) {
    throw new Exception('Mailer failed to send.');
  }

  echo 'OK';
} catch (Exception $e) {
  $errorMessage = $mail->ErrorInfo ?: $e->getMessage();
  error_log('PHPMailer Error: ' . $errorMessage);
  http_response_code(500);
  exit('Email could not be sent. Please try again later.');
}
?>
