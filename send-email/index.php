<?php

require '../vendor/autoload.php';

use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dotenv = Dotenv::createImmutable('../');
$dotenv->safeLoad();

$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestOrigin = rtrim($requestOrigin, '/');

$allowedOrigins = $_ENV['ALLOWED_ORIGINS'] ?? '';
$allowedOriginsArray = explode(',', $allowedOrigins);

if (in_array($requestOrigin, $allowedOriginsArray)) {
    header("Access-Control-Allow-Origin: $requestOrigin");
    header("Access-Control-Allow-Methods: POST, OPTIONS"); // Changed GET to POST
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
} else {
    http_response_code(403);
    echo json_encode(['error' => 'Origin not allowed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    if (!$data || !isset($data->email) || !isset($data->message)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input data']);
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = $_ENV['SMTP_SECURE']; 
        $mail->Port       = $_ENV['SMTP_PORT'];

        $mail->setFrom($_ENV['SMTP_USER'], 'Website Contact Form');
        $mail->addAddress($_ENV['RECEIVER_EMAIL']); 
        $mail->addReplyTo($data->email, $data->name);

        $mail->isHTML(true);
        $mail->Subject = $data->subject;
        $mail->Body    = "<strong>Name:</strong> " . htmlspecialchars($data->name ?? 'N/A') . "<br>" .
                         "<strong>Email:</strong> " . htmlspecialchars($data->email) . "<br><br>" .
                         "<strong>Message:</strong><br>" . nl2br(htmlspecialchars($data->message));

        $mail->send();
        
        echo json_encode(['status' => 'success', 'message' => 'Message has been sent']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => "Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}