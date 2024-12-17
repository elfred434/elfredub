<?php
// Démarrer la session
session_start();

// Paramètres de connexion à la base de données
$host = 'localhost';
$db   = 'blog';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Configuration des cookies permanents
$cookie_expiration = time() + (365 * 24 * 60 * 60); // 1 an
$cookie_path = '/';
$cookie_domain = ''; // Laissez vide pour le domaine actuel
$cookie_secure = false; // true si HTTPS uniquement
$cookie_httponly = true;

try {
    // Connexion à la base de données avec PDO
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Vérifier si le formulaire est soumis
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Récupérer les données du formulaire
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Préparer la requête de sélection
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Vérifier si l'utilisateur existe et le mot de passe est correct
        if ($user && password_verify($password, $user['password'])) {
            // Générer un token unique pour l'authentification
            $auth_token = bin2hex(random_bytes(32));

            // Stocker le token dans la base de données
            $update_token_stmt = $pdo->prepare("UPDATE users SET auth_token = ? WHERE id = ?");
            $update_token_stmt->execute([$auth_token, $user['id']]);

            // Créer des cookies permanents
            setcookie('user_id', $user['id'], $cookie_expiration, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);
            setcookie('username', $user['username'], $cookie_expiration, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);
            setcookie('auth_token', $auth_token, $cookie_expiration, $cookie_path, $cookie_domain, $cookie_secure, $cookie_httponly);

            // Créer des variables de session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['authenticated'] = true;

            // Rediriger vers la page index
            header("Location: index.php");
            exit();
        } else {
            // Identifiants incorrects
            $error_message = "Email ou mot de passe incorrect.";
        }
    }
} catch (PDOException $e) {
    // Gestion des erreurs de base de données
    die("Erreur de connexion : " . $e->getMessage());
}
?>
