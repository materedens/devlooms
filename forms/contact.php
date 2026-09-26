<?php
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

$recipient = 'devloom62@gmail.com';
$headers = [
  'From: DevLoom website <' . $recipient . '>',
  'Reply-To: ' . $email,
  'Content-Type: text/plain; charset=UTF-8',
];
$body = "Name: {$name}\nEmail: {$email}\n\n{$message}";

if (!mail($recipient, $subject, $body, implode("\r\n", $headers))) {
  http_response_code(500);
  exit('Email could not be sent. Configure SMTP in PHP before using the contact form.');
}

echo 'OK';
?>
