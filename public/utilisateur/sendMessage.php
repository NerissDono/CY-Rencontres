<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$sender = $_SESSION['email'];
$recipient = $data['recipient'];
$messageContent = $data['messageContent'];

if (empty($sender) || empty($recipient) || empty($messageContent)) {
    echo json_encode(['success' => false, 'message' => 'Données incomplètes.']);
    exit;
}

// Chemin vers le dossier de l'utilisateur
$userDir = '../../data/messages/' . $sender;
if (!file_exists($userDir)) {
    mkdir($userDir, 0777, true);
}

// Chemin vers le dossier de réception du destinataire
$recipientDir = '../../data/messages/' . $recipient;
if (!file_exists($recipientDir)) {
    mkdir($recipientDir, 0777, true);
}

// Chemin vers le fichier de messages
$senderMessageFile = $userDir . '/' . $recipient . '.txt';
$recipientMessageFile = $recipientDir . '/' . $sender . '.txt';

// Format du message
$message = [
    'timestamp' => date('Y-m-d H:i:s'),
    'sender' => $sender,
    'content' => $messageContent
];

// Ajouter le message aux fichiers des deux utilisateurs
file_put_contents($senderMessageFile, json_encode($message) . PHP_EOL, FILE_APPEND);
file_put_contents($recipientMessageFile, json_encode($message) . PHP_EOL, FILE_APPEND);

echo json_encode(['success' => true]);
