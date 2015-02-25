<?php

// http://blog.teamtreehouse.com/create-ajax-contact-form

$name = strip_tags(trim($_POST['f-name']));
$email = filter_var(trim($_POST['f-email']), FILTER_SANITIZE_EMAIL);
$message = trim($_POST['f-message']);

$recipient = 'cory@launchboxhq.com';
$subject = 'Message from CorySimmons.com';

$email_content = "Name: $name\n";
$email_content .= "Email: $email\n\n";
$email_content .= "Message:\n$message\n";

$email_headers = "From: $name <$email>";

if (mail($recipient, $subject, $email_content, $email_headers)) {
  http_response_code(200);
  echo "Thank You! Your message has been sent.";
} else {
  http_response_code(500);
  echo "Oops! Something went wrong and we couldn't send your message.";
}
