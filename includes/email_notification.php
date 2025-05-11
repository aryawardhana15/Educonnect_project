<?php
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendMentoringReminder($user_email, $user_name, $session_date, $session_time, $topic, $mentor_name, $meeting_link) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_USERNAME, 'EduConnect');
        $mail->addAddress($user_email, $user_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Pengingat Sesi Mentoring - ' . $topic;
        
        $body = "
        <h2>Halo {$user_name},</h2>
        <p>Ini adalah pengingat untuk sesi mentoring Anda yang akan datang:</p>
        <ul>
            <li><strong>Topik:</strong> {$topic}</li>
            <li><strong>Mentor:</strong> {$mentor_name}</li>
            <li><strong>Tanggal:</strong> {$session_date}</li>
            <li><strong>Waktu:</strong> {$session_time}</li>
            <li><strong>Link Meeting:</strong> <a href='{$meeting_link}'>{$meeting_link}</a></li>
        </ul>
        <p>Jangan lupa untuk mempersiapkan pertanyaan dan materi yang ingin didiskusikan.</p>
        <p>Terima kasih,<br>Tim EduConnect</p>
        ";
        
        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

function sendMentoringConfirmation($user_email, $user_name, $session_date, $session_time, $topic, $mentor_name) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        
        // Recipients
        $mail->setFrom(SMTP_USERNAME, 'EduConnect');
        $mail->addAddress($user_email, $user_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Konfirmasi Booking Sesi Mentoring';
        
        $body = "
        <h2>Halo {$user_name},</h2>
        <p>Booking sesi mentoring Anda telah dikonfirmasi:</p>
        <ul>
            <li><strong>Topik:</strong> {$topic}</li>
            <li><strong>Mentor:</strong> {$mentor_name}</li>
            <li><strong>Tanggal:</strong> {$session_date}</li>
            <li><strong>Waktu:</strong> {$session_time}</li>
        </ul>
        <p>Anda akan menerima email reminder 1 jam sebelum sesi dimulai.</p>
        <p>Terima kasih,<br>Tim EduConnect</p>
        ";
        
        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
} 