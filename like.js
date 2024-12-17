/*document.addEventListener('DOMContentLoaded', function() {
    // Commentaires modal logic (from previous implementation)
    // ... (previous modal code remains the same) ...

    // Gestion des Likes
    document.querySelectorAll('.like-button').forEach(likeButton => {
        likeButton.addEventListener('click', function() {
            const publicationId = this.getAttribute('data-publication-id');
            const heartIcon = this.querySelector('i');
            const likesCountSpan = this.querySelector('.likes-count');

            fetch('likes.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=toggle_like&publication_id=${publicationId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'liked') {
                        this.classList.add('liked');
                        heartIcon.classList.replace('bi-heart', 'bi-heart-fill');
                    } else {
                        this.classList.remove('liked');
                        heartIcon.classList.replace('bi-heart-fill', 'bi-heart');
                    }
                    
                    // Mettre à jour le nombre de likes
                    likesCountSpan.textContent = data.total_likes;
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Erreur lors de la gestion du like');
            });
        });
    });
});*/
document.addEventListener('DOMContentLoaded', function() {
    // Select all like buttons
    const likeButtons = document.querySelectorAll('.like-button');

    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const publicationId = this.getAttribute('data-publication-id');
            const likesCountSpan = this.querySelector('.likes-count');
            const icon = this.querySelector('i');

            // Prepare form data
            const formData = new FormData();
            formData.append('publication_id', publicationId);
            formData.append('action', 'toggle_like');

            // Send like/unlike request
            fetch('like.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const currentLikeCount = parseInt(likesCountSpan.textContent);
                    
                    if (data.liked) {
                        // Liked
                        this.classList.add('liked');
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                        likesCountSpan.textContent = currentLikeCount + 1;
                    } else {
                        // Unliked
                        this.classList.remove('liked');
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');
                        likesCountSpan.textContent = currentLikeCount - 1;
                    }
                } else {
                    // Handle error
                    alert(data.message || 'Erreur lors de la gestion du like');
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                //alert('Une erreur  gestion est survenue');
            });
        });
    });
});