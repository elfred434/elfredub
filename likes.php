<?php
// likes.php
require_once 'authentification.php' ;

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Connexion requise']);
    exit();
}

try {
    // Paramètres de connexion à la base de données
    $host = 'localhost';
    $db   = 'blog';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Créer la table des likes si elle n'existe pas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            publication_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (publication_id, user_id),
            FOREIGN KEY (publication_id) REFERENCES publications(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
        )
    ");

    // Gérer les actions de like
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'toggle_like') {
            $publication_id = $_POST['publication_id'] ?? null;

            // Vérifier si l'utilisateur a déjà liké cette publication
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as like_count 
                FROM likes 
                WHERE publication_id = ? AND user_id = ?
            ");
            $stmt->execute([$publication_id, $_SESSION['user_id']]);
            $existingLike = $stmt->fetch();

            if ($existingLike['like_count'] > 0) {
                // Supprimer le like
                $stmt = $pdo->prepare("
                    DELETE FROM likes 
                    WHERE publication_id = ? AND user_id = ?
                ");
                $stmt->execute([$publication_id, $_SESSION['user_id']]);
                $action = 'unliked';
            } else {
                // Ajouter un like
                $stmt = $pdo->prepare("
                    INSERT INTO likes (publication_id, user_id) 
                    VALUES (?, ?)
                ");
                $stmt->execute([$publication_id, $_SESSION['user_id']]);
                $action = 'liked';
            }

            // Compter le nombre total de likes pour cette publication
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as total_likes 
                FROM likes 
                WHERE publication_id = ?
            ");
            $stmt->execute([$publication_id]);
            $totalLikes = $stmt->fetch()['total_likes'];

            echo json_encode([
                'success' => true, 
                'action' => $action,
                'total_likes' => $totalLikes
            ]);
            exit();
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}