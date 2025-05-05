<?php
/**
 * Upload View
 * Allows users to upload photos and view their previously uploaded photos
 */
$pageTitle = 'Upload Photos';
require_once 'includes/header.php';
?>

<div class="container py-4">
    <h1 class="mb-4">Upload Photos</h1>
    
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Upload a New Photo</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" action="?page=upload">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="photo" class="form-label">Select Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg,image/png,image/gif" required>
                            <div class="form-text">Max file size: 5MB. Allowed types: jpg, jpeg, png, gif</div>
                        </div>
                        
                        <div class="mb-3">
                            <div id="imagePreview" class="mt-2" style="display: none;">
                                <h6>Preview:</h6>
                                <img id="previewImg" class="img-fluid img-thumbnail" alt="Image preview">
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Upload Photo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <h2 class="mb-3">My Photos</h2>
            
            <?php if (empty($photos)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>You haven't uploaded any photos yet.
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($photos as $photo): ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="<?= SITE_URL . '/' . $photo['photo_path'] ?>" class="card-img-top" alt="<?= htmlspecialchars($photo['title']) ?>" style="height: 200px; object-fit: cover;" onerror="this.src='<?= SITE_URL ?>/assets/images/placeholder.svg'">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($photo['title']) ?></h5>
                                    <?php if (!empty($photo['description'])): ?>
                                        <p class="card-text"><?= htmlspecialchars($photo['description']) ?></p>
                                    <?php endif; ?>
                                    <p class="card-text"><small class="text-muted">Uploaded: <?= formatDate($photo['created_at']) ?></small></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const photoInput = document.getElementById('photo');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    // Show image preview when a file is selected
    if (photoInput) {
        photoInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                
                reader.readAsDataURL(this.files[0]);
            } else {
                imagePreview.style.display = 'none';
            }
        });
    }
});
</script>

<?php
require_once 'includes/footer.php';
?> 