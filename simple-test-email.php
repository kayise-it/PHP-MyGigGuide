<?php
/**
 * Simple Test Email Script
 * Uses PHP's mail() function directly
 */

$to = 'thandov.hlophe@gmail.com';
$subject = 'Test Email from MyGigGuide';
$message = "This is a test email from MyGigGuide to verify email configuration is working.\n\n";
$message .= "Sent at: " . date('Y-m-d H:i:s') . "\n";
$message .= "Server: " . gethostname() . "\n";

$headers = "From: noreply@mygigguide.co.za\r\n";
$headers .= "Reply-To: noreply@mygigguide.co.za\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

echo "Sending test email to: $to\n";
echo "Subject: $subject\n\n";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ Email sent successfully!\n";
    echo "Check the recipient's inbox (and spam folder) for the email.\n";
    echo "\nTo check mail logs: sudo tail -f /var/log/mail.log\n";
} else {
    echo "❌ Failed to send email.\n";
    echo "\nTroubleshooting:\n";
    echo "1. Check if Postfix is running: sudo systemctl status postfix\n";
    echo "2. Check mail logs: sudo tail -f /var/log/mail.log\n";
    echo "3. Verify Postfix configuration\n";
    exit(1);
}
