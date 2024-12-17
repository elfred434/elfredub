<?php
session_start(); // Démarrer la session au début du script

// Database connection
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

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $termsAccepted = isset($_POST['termsCheck']) ? 1 : 0;

    // Validate input (you should add more robust validation)
    if (strlen($username) < 4 || strlen($password) < 8) {
        die("Invalid username or password");
    }

    // Hash the password (IMPORTANT: use strong hashing)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL to prevent SQL injection
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, terms_accepted) VALUES (?, ?, ?, ?)");

    try {
        // Execute the prepared statement
        $result = $stmt->execute([$username, $email, $hashedPassword, $termsAccepted]);

        if ($result) {
            // Stocker l'email et le mot de passe (en texte brut) dans la session
            // ATTENTION : en production, ne stockez JAMAIS le mot de passe en texte brut
            $_SESSION['registration_email'] = $email;
            $_SESSION['registration_password'] = $password;

            // Rediriger vers la page de connexion
            header("Location: connexion.php");
            exit();
        } else {
            echo "Erreur lors de l'inscription";
        }
    } catch (PDOException $e) {
        // Handle potential duplicate username/email
        if ($e->getCode() == '23000') {
            echo "Ce nom d'utilisateur ou email existe déjà.";
        } else {
            echo "Erreur d'inscription : " . $e->getMessage();
        }
    }
}
?>