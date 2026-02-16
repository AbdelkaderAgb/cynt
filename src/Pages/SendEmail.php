<?php
/**
 * CYN Tourism - Send Document via Email
 * Handles sending vouchers and invoices to customers via email
 */
require_once dirname(__DIR__, 2) . '/config/config.php'; require_once dirname(__DIR__, 2) . '/src/Core/Auth.php';
require_once dirname(__DIR__, 2) . '/src/Core/helpers.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

if (!Auth::check()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$emailTo = filter_var($_POST['email_to'] ?? '', FILTER_VALIDATE_EMAIL);
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');
$type = $_POST['type'] ?? '';
$id = intval($_POST['id'] ?? 0);
$docTitle = $_POST['doc_title'] ?? '';
$docNumber = $_POST['doc_number'] ?? '';

if (!$emailTo) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address']);
    exit;
}

if (!$id || !$type) {
    echo json_encode(['success' => false, 'error' => 'Missing document information']);
    exit;
}

// Load email configuration
$emailConfig = Database::getInstance()->fetchOne("SELECT * FROM email_config LIMIT 1");

if (empty($emailConfig) || empty($emailConfig['smtp_host'])) {
    echo json_encode(['success' => false, 'error' => 'Email configuration not set. Please configure SMTP settings first.']);
    exit;
}

// Build the document URL for reference
$documentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . dirname($_SERVER['REQUEST_URI'])
    . '/export.php?type=' . urlencode($type) . '&id=' . $id;

// Build email body
$companyName = defined('COMPANY_NAME') ? COMPANY_NAME : 'CYN Turizm';
$emailBody = buildEmailBody($docTitle, $docNumber, $message, $documentUrl, $companyName);

// Send email using configured SMTP
try {
    $result = sendSmtpEmail(
        $emailConfig,
        $emailTo,
        $subject ?: $docTitle . ' - ' . $docNumber,
        $emailBody
    );
    
    if ($result) {
        // Log the email send
        try {
            Database::getInstance()->query(
                "INSERT INTO email_log (recipient, subject, document_type, document_id, sent_by, sent_at) VALUES (?, ?, ?, ?, ?, NOW())",
                [$emailTo, $subject, $type, $id, $_SESSION['user_id'] ?? 0]
            );
        } catch (Exception $e) {
            // Log table might not exist, ignore
        }
        
        echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send email']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function buildEmailBody($docTitle, $docNumber, $customMessage, $documentUrl, $companyName) {
    $customSection = $customMessage ? '<p style="background:#f8fafc;padding:16px;border-radius:8px;border-left:4px solid #3b82f6;margin:20px 0">' . nl2br(htmlspecialchars($customMessage)) . '</p>' : '';
    
    return '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,sans-serif;line-height:1.6;color:#1e293b;max-width:600px;margin:0 auto;padding:20px">
<div style="background:linear-gradient(135deg,#3b82f6 0%,#06b6d4 100%);padding:30px;border-radius:12px 12px 0 0;text-align:center">
<h1 style="color:white;margin:0;font-size:24px">' . htmlspecialchars($companyName) . '</h1>
</div>
<div style="background:white;padding:30px;border:1px solid #e2e8f0;border-top:none;border-radius:0 0 12px 12px">
<h2 style="color:#1e293b;margin-top:0">📄 ' . htmlspecialchars($docTitle) . '</h2>
<p style="color:#64748b">Belge Numarasi: <strong style="color:#3b82f6;font-family:monospace">' . htmlspecialchars($docNumber) . '</strong></p>
' . $customSection . '
<p>Belgenizi goruntulemek veya indirmek icin asagidaki butona tiklayiniz:</p>
<div style="text-align:center;margin:30px 0">
<a href="' . htmlspecialchars($documentUrl) . '" style="display:inline-block;background:#3b82f6;color:white;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:600">Belgeyi Goruntule</a>
</div>
<hr style="border:none;border-top:1px solid #e2e8f0;margin:24px 0">
<p style="color:#94a3b8;font-size:12px;text-align:center;margin:0">
Bu email ' . htmlspecialchars($companyName) . ' tarafindan gonderilmistir.<br>
Eger bu emaili beklemiyorsaniz, lutfen dikkate almayin.
</p>
</div>
</body>
</html>';
}

function sendSmtpEmail($config, $to, $subject, $body) {
    $host = $config['smtp_host'];
    $port = $config['smtp_port'] ?? 587;
    $username = $config['smtp_username'];
    $password = $config['smtp_password'];
    $fromEmail = $config['from_email'] ?? $username;
    $fromName = $config['from_name'] ?? 'CYN Turizm';
    
    // Try to use PHPMailer if available
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        return sendWithPhpMailer($host, $port, $username, $password, $fromEmail, $fromName, $to, $subject, $body);
    }
    
    // Fallback to mail() function with basic headers
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'X-Mailer: CYN Tourism System'
    ];
    
    return @mail($to, $subject, $body, implode("\r\n", $headers));
}

function sendWithPhpMailer($host, $port, $username, $password, $fromEmail, $fromName, $to, $subject, $body) {
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = $port == 465 ? 'ssl' : 'tls';
        $mail->Port = $port;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        return $mail->send();
    } catch (Exception $e) {
        throw new Exception('Email error: ' . $mail->ErrorInfo);
    }
}
