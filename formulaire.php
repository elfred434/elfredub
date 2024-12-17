
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer une Publication</title>
    <link href="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .preview-image {
            max-width: 150px;
            max-height: 150px;
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2>Créer une Publication</h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <form id="publicationForm" method="post" enctype="multipart/form-data" action="pub.php">
                            <!-- Contenu textuel -->
                            <div class="mb-3">
                                <label for="content" class="form-label">Votre message</label>
                                <textarea class="form-control" id="content" name="content" rows="4" placeholder="Quoi de neuf ?"></textarea>
                            </div>

                            <!-- Upload de photos -->
                            <div class="mb-3">
                                <label for="photos" class="form-label">Photos</label>
                                <input 
                                    type="file" 
                                    class="form-control" 
                                    id="photos" 
                                    name="photos[]" 
                                    accept="image/*" 
                                    multiple
                                >
                                <div id="photoPreview" class="d-flex flex-wrap mt-2"></div>
                            </div>

                            <!-- Section Vidéo -->
                            <div class="mb-3">
                                <label class="form-label">Vidéo</label>
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="video_type" 
                                        id="video_type_none" 
                                        value="" 
                                        checked
                                    >
                                    <label class="form-check-label" for="video_type_none">
                                        Aucune vidéo
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="video_type" 
                                        id="video_type_upload" 
                                        value="upload"
                                    >
                                    <label class="form-check-label" for="video_type_upload">
                                        Télécharger une vidéo
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input 
                                        class="form-check-input" 
                                        type="radio" 
                                        name="video_type" 
                                        id="video_type_url" 
                                        value="url"
                                    >
                                    <label class="form-check-label" for="video_type_url">
                                        Lien vidéo
                                    </label>
                                </div>

                                <!-- Upload vidéo -->
                                <div id="video_upload_section" class="mt-2" style="display:none;">
                                    <input 
                                        type="file" 
                                        class="form-control" 
                                        id="video_upload" 
                                        name="video_upload" 
                                        accept="video/*"
                                    >
                                </div>

                                <!-- URL Vidéo -->
                                <div id="video_url_section" class="mt-2" style="display:none;">
                                    <input 
                                        type="url" 
                                        class="form-control" 
                                        id="video_url" 
                                        name="video_url" 
                                        placeholder="Coller le lien de la vidéo"
                                    >
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Publier</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gestion dynamique des sections vidéo
        document.querySelectorAll('input[name="video_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.getElementById('video_upload_section').style.display = 
                    this.value === 'upload' ? 'block' : 'none';
                
                document.getElementById('video_url_section').style.display = 
                    this.value === 'url' ? 'block' : 'none';
            });
        });

        // Prévisualisation des images
        document.getElementById('photos').addEventListener('change', function(event) {
            const preview = document.getElementById('photoPreview');
            preview.innerHTML = ''; // Effacer les aperçus précédents

            Array.from(this.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.classList.add('preview-image');
                    preview.appendChild(img);
                }
                reader.readAsDataURL(file);
            });
        });
    </script>

    <script src="bootstrap-5.3.3-dist/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>