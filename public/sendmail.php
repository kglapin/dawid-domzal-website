<?php
header('Content-Type: application/json; charset=utf-8');

$response = ['success' => false, 'message' => 'Nieznany błąd.'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Niedozwolona metoda.';
    echo json_encode($response);
    exit;
}

// Honeypot: pole "website" musi być puste
if (!empty($_POST['website'] ?? '')) {
    $response['message'] = 'Wykryto spam.';
    echo json_encode($response);
    exit;
}

// Captcha matematyczna
$captcha = trim($_POST['captcha'] ?? '');
$expected = trim($_POST['captcha-expected'] ?? '');
if ($captcha === '' || $captcha !== $expected) {
    $response['message'] = 'Niepoprawna odpowiedź weryfikacyjna.';
    echo json_encode($response);
    exit;
}

// RODO zgoda
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

$to = 'domzal-dawid@wp.pl';
$subject = 'Nowe zapytanie ze strony adrgeo.pl';
$body = "Imię i nazwisko: $name\n";
$body .= "Telefon: $phone\n";
$body .= "E-mail: $email\n\n";
$body .= "Opis:\n$message\n\n";
$body .= "Zgoda RODO: tak";

$replyTo = $email !== '' ? $email : 'kontakt@adrgeo.pl';
$from = 'formularz@adrgeo.pl';

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/plain; charset=UTF-8\r\n";
$headers .= "From: $from\r\n";
$headers .= "Reply-To: $replyTo\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$subjectEncoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';

if (mail($to, $subjectEncoded, $body, $headers)) {
    $response = ['success' => true, 'message' => 'Dziękuję za wiadomość. Odezwę się najszybciej jak to możliwe.'];
} else {
    $response['message'] = 'Nie udało się wysłać wiadomości. Spróbuj ponownie lub zadzwoń: 501 719 855.';
}

echo json_encode($response);
exit;
