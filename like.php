<?php

require_once 'authentification.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Connectez-vous pour liker']);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=blog;charset=utf8mb4", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $publicationId = $_POST['publication_id'];
    $userId = $_SESSION['user_id'];

    // Check if like already exists
    $stmt = $pdo->prepare("SELECT * FROM likes WHERE user_id = ? AND publication_id = ?");
    $stmt->execute([$userId, $publicationId]);
    $existingLike = $stmt->fetch();

    if ($existingLike) {
        // Unlike
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND publication_id = ?");
        $stmt->execute([$userId, $publicationId]);
        echo json_encode(['success' => true, 'liked' => false]);
    } else {
        // Like
        $stmt = $pdo->prepare("INSERT INTO likes (user_id, publication_id) VALUES (?, ?)");
        $stmt->execute([$userId, $publicationId]);
        echo json_encode(['success' => true, 'liked' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur de base de données']);
}