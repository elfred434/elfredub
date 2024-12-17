<?php
// Fichier: comments.php (à inclure ou à intégrer dans index.php)
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

    // Créer la table des commentaires si elle n'existe pas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS commentaires (
            id INT AUTO_INCREMENT PRIMARY KEY,
            publication_id INT NOT NULL,
            user_id INT NOT NULL,
            contenu TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (publication_id) REFERENCES publications(id),
            FOREIGN KEY (user_id) REFERENCES utilisateurs(id)
        )
    ");

    // Gérer l'ajout de commentaires
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';

        if ($action === 'ajouter_commentaire') {
            $publication_id = $_POST['publication_id'] ?? null;
            $contenu = trim($_POST['contenu'] ?? '');

            if (empty($contenu)) {
                echo json_encode(['success' => false, 'message' => 'Le commentaire ne peut pas être vide']);
                exit();
            }

            $stmt = $pdo->prepare("
                INSERT INTO commentaires (publication_id, user_id, contenu) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$publication_id, $_SESSION['user_id'], $contenu]);

            echo json_encode(['success' => true, 'message' => 'Commentaire ajouté']);
            exit();
        }

        // Récupérer les commentaires
        if ($action === 'charger_commentaires') {
            $publication_id = $_POST['publication_id'] ?? null;

            $stmt = $pdo->prepare("
                SELECT c.*, u.username 
                FROM commentaires c
                JOIN users u ON c.user_id = u.id
                WHERE c.publication_id = ?
                ORDER BY c.created_at ASC
            ");
            $stmt->execute([$publication_id]);
            $commentaires = $stmt->fetchAll();

            echo json_encode([
                'success' => true, 
                'commentaires' => $commentaires
            ]);
            exit();
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}