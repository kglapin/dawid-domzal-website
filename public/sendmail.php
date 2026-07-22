<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => 'Nieznany błąd.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Niedozwolona metoda.';
    echo json_encode($response);
    exit;
}

// Honeypot
if (!empty($_POST['website'] ?? '')) {
    $response['message'] = 'Wykryto spam.';
    echo json_encode($response);
    exit;
}

// Captcha
$captcha = trim($_POST['captcha'] ?? '');
$expected = trim($_POST['captcha-expected'] ?? '');
if ($captcha === '' || $captcha !== $expected) {
    $response['message'] = 'Niepoprawna odpowiedź weryfikacyjna.';
    echo json_encode($response);
    exit;
}

// RODO
if (empty($_POST['zgoda_rodo'])) {
    $response['message'] = 'Wymagana zgoda na przetwarzanie danych.';
    echo json_encode($response);
    exit;
}

// Pola wymagane
$name = trim($_POST['Imie_i_nazwisko'] ?? '');
$phone = trim($_POST['Telefon'] ?? '');
$email = trim($_POST['Email'] ?? '');
$message = trim($_POST['Opis'] ?? '');

if ($name === '' || $phone === '') {
    $response['message'] = 'Imię i numer telefonu są wymagane.';
    echo json_encode($response);
    exit;
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Podano nieprawidłowy adres e-mail.';
    echo json_encode($response);
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.zenbox.pl';
    $mail->SMTPAuth = true;
    $mail->Username = 'noreply@adrgeo.pl';
    $mail->Password = '91@gKc#yUpCa';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('noreply@adrgeo.pl', 'Formularz adrgeo.pl');
    $mail->addAddress('domzal-dawid@wp.pl');
    if ($email !== '') {
        $mail->addReplyTo($email, $name);
    }

    $mail->isHTML(false);
    $mail->Subject = 'Nowe zapytanie ze strony adrgeo.pl';
    $mail->Body = "Imię i nazwisko: $name\n"
                . "Telefon: $phone\n"
                . "E-mail: $email\n\n"
                . "Opis:\n$message\n\n"
                . "Zgoda RODO: tak";

    $mail->send();
    $response = ['success' => true, 'message' => 'Dziękuję za wiadomość. Odezwę się najszybciej jak to możliwe.'];
} catch (Exception $e) {
    $response['message'] = 'Nie udało się wysłać wiadomości. Spróbuj ponownie lub zadzwoń: 501 719 855.';
    // $response['debug'] = $mail->ErrorInfo; // odkomentuj do diagnostyki
}

echo json_encode($response);
exit;
