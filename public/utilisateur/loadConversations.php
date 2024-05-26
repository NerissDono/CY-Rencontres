<?php
session_start();
header('Content-Type: application/json');

$userEmail = $_SESSION['email'];

// Chemin vers le dossier de l'utilisateur
$userDir = '../../data/messages/' . $userEmail;
$conversations = [];

if (is_dir($userDir)) {
    $files = glob($userDir . '/*.txt');
    foreach ($files as $file) {
        $recipient = basename($file, '.txt');
        $messages = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lastMessage = json_decode(end($messages), true);

        $conversations[] = [
            'recipient' => $recipient,
            'lastMessage' => $lastMessage['content']
        ];
    }
}

echo json_encode(['conversations' => $conversations]);
