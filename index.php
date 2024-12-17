<?php
require_once 'authentification.php' ;
/*session_start();*/

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
      SELECT 
            p.*, 
            u.username,
            (SELECT COUNT(*) FROM likes l WHERE l.publication_id = p.id) as total_likes,
            (SELECT COUNT(*) FROM likes l WHERE l.publication_id = p.id AND l.user_id = ?) as user_liked
        FROM publications p
        JOIN users u ON p.user_id = u.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
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
    <title>Social Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --text-color: #2c3e50;
            --background-color: #f4f6f7;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--text-color);
        }

        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.075);
            padding: 1rem;
        }

        .publication-image {
            border-radius: 8px;
            max-height: 300px;
            width: 100%;
            object-fit: cover;
        }

        .publication-video {
            border-radius: 8px;
            max-height: 400px;
            width: 100%;
        }

        .like-button {
            color: var(--text-color);
            transition: all 0.2s ease;
        }

        .like-button:hover {
            color: var(--primary-color);
        }

        .like-button.liked {
            color: #e74c3c;
        }

        .btn-new-post {
            background-color: var(--primary-color);
            color: white;
            transition: all 0.3s ease;
        }

        .btn-new-post:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .commentaire {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.5rem;
        }

        .modal-content {
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="fw-bold">Publications</h1>
                    <a href="formulaire.php" class="btn btn-new-post">
                        <i class="bi bi-plus-circle me-2"></i>Nouvelle Publication
                    </a>
                </div>

                <?php if (isset($_GET['message']) && $_GET['message'] == 'publication_success'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        Votre publication a été créée avec succès !
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($publications)): ?>
                    <div class="alert alert-info text-center">
                        <i class="bi bi-info-circle me-2"></i>Aucune publication pour le moment.
                    </div>
                <?php else: ?>
                    <?php foreach ($publications as $publication): ?>
                        <div class="card mb-4 overflow-hidden">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                   
                                    <div>
                                        <h6 class="mb-0 fw-bold"><?php echo htmlspecialchars($publication['username']); ?></h6>
                                        <small class="text-muted">
                                            <?php 
                                            $date = new DateTime($publication['created_at']);
                                            echo $date->format('d/m/Y H:i'); 
                                            ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($publication['content'])): ?>
                                    <p class="card-text"><?php echo htmlspecialchars($publication['content']); ?></p>
                                <?php endif; ?>

                                <!-- Photos et vidéos (code similaire à l'original) -->
                                <!-- ... (existing photo and video rendering code) ... -->

                                <div class="mt-3 d-flex justify-content-between align-items-center border-top pt-3">
                                    <div class="d-flex align-items-center">
                                        <span 
                                            class="like-button <?php echo $publication['user_liked'] > 0 ? 'liked' : ''; ?>" 
                                            data-publication-id="<?php echo $publication['id']; ?>"
                                        >
                                            <i class="bi bi-heart<?php echo $publication['user_liked'] > 0 ? '-fill' : ''; ?> me-2"></i>
                                            <?php echo $publication['total_likes']; ?> Likes
                                        </span>
                                    </div>
                                    <button 
                                        class="btn btn-outline-secondary btn-sm btn-commentaires" 
                                        data-publication-id="<?php echo $publication['id']; ?>"
                                    >
                                        <i class="bi bi-chat-left-text me-2"></i>Commentaires
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="commentairesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Commentaires</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="listeCommentaires" class="mb-3"></div>
                    <form id="formCommentaire">
                        <input type="hidden" id="publicationId" name="publication_id">
                        <div class="mb-3">
                            <textarea 
                                class="form-control" 
                                id="contenuCommentaire" 
                                name="contenu" 
                                rows="3" 
                                placeholder="Votre commentaire..."
                            ></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Commentaires (reste similaire à l'original) -->
    <!-- ... (existing modal code) ... -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="like.js"></script>
    <script>
        // Votre script JavaScript existant reste essentiellement le même
        // ... (existing JavaScript code) ...
        document.addEventListener('DOMContentLoaded', function() {
        const commentairesModal = new bootstrap.Modal(document.getElementById('commentairesModal'));
        const listeCommentaires = document.getElementById('listeCommentaires');
        const formCommentaire = document.getElementById('formCommentaire');
        const publicationIdInput = document.getElementById('publicationId');
        const contenuCommentaire = document.getElementById('contenuCommentaire');

        // Gestionnaire pour boutons Commentaires
        document.querySelectorAll('.btn-commentaires').forEach(btn => {
            btn.addEventListener('click', function() {
                const publicationId = this.getAttribute('data-publication-id');
                publicationIdInput.value = publicationId;
                
                // Charger les commentaires existants
                fetch('comments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `action=charger_commentaires&publication_id=${publicationId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        listeCommentaires.innerHTML = data.commentaires.length > 0 
                            ? data.commentaires.map(comm => `
                                <div class="commentaire">
                                    <strong>${comm.username}</strong>
                                    <small class="text-muted float-end">${new Date(comm.created_at).toLocaleString()}</small>
                                    <p>${comm.contenu}</p>
                                </div>
                            `).join('') 
                            : '<p class="text-muted">Aucun commentaire</p>';
                        
                        commentairesModal.show();
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Erreur lors du chargement des commentaires');
                });
            });
        });

        // Soumission du formulaire de commentaire
        formCommentaire.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'ajouter_commentaire');

            fetch('comments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recharger les commentaires
                    document.querySelector('.btn-commentaires[data-publication-id="' + publicationIdInput.value + '"]').click();
                    contenuCommentaire.value = ''; // Vider le textarea
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de l\'envoi du commentaire');
            });
        });
    });
    </script>
</body>
</html>