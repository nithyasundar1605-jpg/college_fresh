<?php
// Run this script from the command line: php test_email_connection.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once 'backend/libs/PHPMailer/Exception.php';
require_once 'backend/libs/PHPMailer/PHPMailer.php';
require_once 'backend/libs/PHPMailer/SMTP.php';
include_once 'backend/utils/mail_helper.php';

// Check if credentials are still default
$helper_content = file_get_contents('backend/utils/mail_helper.php');
if (strpos($helper_content, "your_email@gmail.com") !== false) {
    echo "\n\033[31m[ERROR] Credentials not configured!\033[0m\n";
    echo "You must open 'backend/utils/mail_helper.php' and replace:\n";
    echo " - 'your_email@gmail.com' -> Your actual Gmail address\n";
    echo " - 'your_app_password'    -> Your 16-character Google App Password\n";
    echo "\nNOTE: Your normal Gmail password will likely NOT work due to security. You need an App Password.\n";
    exit;
}

echo "Attempts to send a test email...\n";

// We'll create a new instance here to force Debug output
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;              // Enable verbose debug output
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    
    // Extract credentials from helper file (crude parsing for test)
    // Actually, let's just ask the user to input them temporarily if they want, 
    // or better, just reuse the logic if we could, but we can't easily injection Debug flag into the helper function.
    // So we will just parse the file to find the credentials they saved.
    
    preg_match("/Username\s*=\s*'([^']+)'/", $helper_content, $user_match);
    preg_match("/Password\s*=\s*'([^']+)'/", $helper_content, $pass_match);
    
    if (empty($user_match[1]) || empty($pass_match[1])) {
        echo "Could not parse credentials from mail_helper.php\n";
        exit;
    }

    $mail->Username   = $user_match[1];
    $mail->Password   = $pass_match[1];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom($user_match[1], 'Test Script');
    $mail->addAddress($user_match[1]); // Send to self

    $mail->isHTML(true);
    $mail->Subject = 'Test Email from College Event System';
    $mail->Body    = 'This is a test email to verify your SMTP configuration. <b>It works!</b>';

    $mail->send();
    echo "\n\033[32m[SUCCESS] Message has been sent!\033[0m\n";
} catch (Exception $e) {
    echo "\n\033[31m[FAILURE] Message could not be sent.\033[0m\n";
    echo "Mailer Error: {$mail->ErrorInfo}\n";
}
?>
