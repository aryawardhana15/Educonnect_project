<?php
require_once('config.php');

// Ambil input
$data = json_decode(file_get_contents('php://input'), true);
$userMessage = $data['message'] ?? '';

if (empty($userMessage)) {
    echo json_encode(['response' => 'Pesan kosong.']);
    exit;
}

// Siapkan data untuk API
$data = [
    'model' => 'llama3-70b-8192',
    'messages' => [
        ['role' => 'user', 'content' => $userMessage]
    ]
];

// Kirim ke Groq API
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, GROQ_API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . GROQ_API_KEY,
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    $error = 'cURL Error: ' . curl_error($ch);
    curl_close($ch);
    echo json_encode(['response' => $error]);
    exit;
}

curl_close($ch);

$responseData = json_decode($response, true);

// Ambil isi balasan dari API
if (isset($responseData['choices'][0]['message']['content'])) {
    $botReply = $responseData['choices'][0]['message']['content'];
} elseif (isset($responseData['error']['message'])) {
    $botReply = 'API Error: ' . $responseData['error']['message'];
} else {
    $botReply = 'No response from API.';
}

// Balikin hasil sebagai JSON
echo json_encode(['response' => $botReply]);
?>
