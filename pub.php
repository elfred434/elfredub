<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
    exit();
}

// Gestion des uploads
function uploadFile($file, $uploadDir, $allowedTypes = [], $maxSize = 5 * 1024 * 1024) {
    // Créer le dossier s'il n'existe pas
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filename = uniqid() . '_' . basename($file['name']);
    $uploadPath = $uploadDir . $filename;
    $fileType = strtolower(pathinfo($uploadPath, PATHINFO_EXTENSION));

    // Vérifications
    if ($file['size'] > $maxSize) {
        throw new Exception("Le fichier est trop volumineux. Taille maximale : " . ($maxSize / 1024 / 1024) . " Mo");
    }

    // Vérification du type de fichier
    if (!empty($allowedTypes) && !in_array($fileType, $allowedTypes)) {
        throw new Exception("Type de fichier non autorisé.");
    }

    // Déplacer le fichier uploadé
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $filename;
    } else {
        throw new Exception("Erreur lors de l'upload du fichier.");
    }
}

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

        // Récupérer les données du formulaire
        $content = trim($_POST['content'] ?? '');
        $videoType = $_POST['video_type'] ?? '';
        $videoUrl = trim($_POST['video_url'] ?? '');
        
        // Chemins de stockage
        $photoDir = 'uploads/photos/';
        $videoDir = 'uploads/videos/';

        // Tableau pour stocker les chemins des médias
        $mediaFiles = [
            'photos' => [],
            'video' => null
        ];

        // Gestion des uploads de photos
        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['name'] as $key => $name) {
                if ($name != '') {
                    $photoTmp = [
                        'name' => $name,
                        'type' => $_FILES['photos']['type'][$key],
                        'tmp_name' => $_FILES['photos']['tmp_name'][$key],
                        'error' => $_FILES['photos']['error'][$key],
                        'size' => $_FILES['photos']['size'][$key]
                    ];

                    $uploadedPhoto = uploadFile(
                        $photoTmp, 
                        $photoDir, 
                        ['jpg', 'jpeg', 'png', 'gif'], 
                        5 * 1024 * 1024 // 5 Mo
                    );
                    $mediaFiles['photos'][] = $uploadedPhoto;
                }
            }
        }

        // Gestion de la vidéo
        if ($videoType == 'upload' && !empty($_FILES['video_upload']['name'])) {
            $uploadedVideo = uploadFile(
                $_FILES['video_upload'], 
                $videoDir, 
                ['mp4', 'avi', 'mov', 'wmv'], 
                50 * 1024 * 1024 // 50 Mo
            );
            $mediaFiles['video'] = $uploadedVideo;
        } elseif ($videoType == 'url' && !empty($videoUrl)) {
            $mediaFiles['video'] = $videoUrl;
        }

        // Préparer la requête d'insertion
        $stmt = $pdo->prepare("INSERT INTO publications (user_id, content, photos, video, video_type, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        
        // Convertir les tableaux en JSON pour le stockage
        $photosJson = !empty($mediaFiles['photos']) ? json_encode($mediaFiles['photos']) : null;
        $videoValue = $mediaFiles['video'];

        // Exécuter l'insertion
        $stmt->execute([
            $_SESSION['user_id'], 
            $content, 
            $photosJson, 
            $videoValue, 
            $videoType
        ]);

        // Redirection avec message de succès
        header("Location: index.php");
        exit();

    } catch (Exception $e) {
        // Gestion des erreurs
        $error_message = $e->getMessage();
    }
}
?>