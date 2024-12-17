<?php
// Démarrer la session
session_start();

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

    // Invalider le token d'authentification si l'utilisateur est connecté
    if (isset($_COOKIE['user_id'])) {
        $stmt = $pdo->prepare("UPDATE users SET auth_token = NULL WHERE id = ?");
        $stmt->execute([$_COOKIE['user_id']]);
    }
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Détruire la session
$_SESSION = array();
session_destroy();

// Supprimer tous les cookies
if (isset($_COOKIE['user_id'])) {
    setcookie('user_id', '', time() - 3600, '/');
}
if (isset($_COOKIE['username'])) {
    setcookie('username', '', time() - 3600, '/');
}
if (isset($_COOKIE['auth_token'])) {
    setcookie('auth_token', '', time() - 3600, '/');
}

// Rediriger vers la page de connexion
header("Location: connexion.php");
exit();
?>