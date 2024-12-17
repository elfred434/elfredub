<?php
session_start();

// Vérifier si l'utilisateur est connecté via les cookies ou la session
$is_authenticated = false;

if (isset($_COOKIE['user_id']) && isset($_COOKIE['auth_token'])) {
    // Paramètres de connexion à la base de données
    $host = 'localhost';
    $db   = 'blog';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    try {
        // Connexion à la base de données
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, $user, $pass, $options);

        // Vérifier le token d'authentification
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND auth_token = ?");
        $stmt->execute([$_COOKIE['user_id'], $_COOKIE['auth_token']]);
        $user = $stmt->fetch();

        if ($user) {
            $is_authenticated = true;
            $username = $user['username'];
        }
    } catch (PDOException $e) {
        // Gestion des erreurs de base de données
        die("Erreur de connexion : " . $e->getMessage());
    }
}

// Rediriger vers la page de connexion si non authentifié
if (!$is_authenticated) {
    header("Location: connexion.php");
    exit();
}
?>
