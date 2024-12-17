<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: connexion.php");
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

    // Récupérer les publications avec les informations de l'utilisateur
    $stmt = $pdo->prepare("
        SELECT p.*, u.username 
        FROM publications p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $publications = $stmt->fetchAll();

} catch (Exception $e) {
    $error_message = $e->getMessage();
    $publications = [];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accueil</title>
    <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .publication-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: cover;
            margin: 5px;
        }
        .publication-video {
            max-width: 100%;
            max-height: 400px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <?php if (isset($_GET['message']) && $_GET['message'] == 'publication_success'): ?>
                    <div class="alert alert-success">
                        Votre publication a été créée avec succès !
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div class="text-end mb-3">
                    <a href="pub.php" class="btn btn-primary">Nouvelle Publication</a>
                </div>

                <?php if (empty($publications)): ?>
                    <div class="alert alert-info">
                        Aucune publication pour le moment.
                    </div>
                <?php else: ?>
                    <?php foreach ($publications as $publication): ?>
                        <div class="card mb-3">
                            <div class="card-header">
                                <strong><?php echo htmlspecialchars($publication['username']); ?></strong>
                                <small class="text-muted float-end">
                                    <?php 
                                    $date = new DateTime($publication['created_at']);
                                    echo $date->format('d/m/Y H:i'); 
                                    ?>
                                </small>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($publication['content'])): ?>
                                    <p><?php echo htmlspecialchars($publication['content']); ?></p>
                                <?php endif; ?>

                                <?php 
                                // Gestion des photos
                                if (!empty($publication['photos'])):
                                    $photos = json_decode($publication['photos'], true);
                                    if (!empty($photos)):
                                ?>
                                    <div class="d-flex flex-wrap">
                                        <?php foreach ($photos as $photo): ?>
                                            <img 
                                                src="uploads/photos/<?php echo htmlspecialchars($photo); ?>" 
                                                class="publication-image" 
                                                alt="Photo de publication"
                                            >
                                        <?php endforeach; ?>
                                    </div>
                                <?php 
                                    endif; 
                                endif; 
                                ?>

                                <?php 
                                // Gestion de la vidéo
                                if (!empty($publication['video'])):
                                    $videoType = $publication['video_type'];
                                ?>
                                    <div class="mt-3">
                                        <?php if ($videoType == 'upload'): ?>
                                            <video controls class="publication-video">
                                                <source 
                                                    src="uploads/videos/<?php echo htmlspecialchars($publication['video']); ?>" 
                                                    type="video/<?php echo pathinfo($publication['video'], PATHINFO_EXTENSION); ?>"
                                                >
                                                Votre navigateur ne supporte pas la lecture de vidéos.
                                            </video>
                                        <?php elseif ($videoType == 'url'): ?>
                                            <div class="embed-responsive embed-responsive-16by9">
                                                <?php 
                                                // Détection et intégration de YouTube
                                                if (strpos($publication['video'], 'youtube.com') !== false || 
                                                    strpos($publication['video'], 'youtu.be') !== false):
                                                    $videoId = '';
                                                    if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $publication['video'], $matches)) {
                                                        $videoId = $matches[1];
                                                    }
                                                ?>
                                                    <iframe 
                                                        class="embed-responsive-item publication-video" 
                                                        src="https://www.youtube.com/embed/<?php echo $videoId; ?>" 
                                                        allowfullscreen
                                                    ></iframe>
                                                <?php else: ?>
                                                    <a href="<?php echo htmlspecialchars($publication['video']); ?>" target="_blank">
                                                        Lien vidéo
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
