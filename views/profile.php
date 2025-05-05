<?php
/**
 * Profile View
 * Allows users to view and edit their profile information
 */
$page_title = "User Profile";
require_once 'includes/header.php';
?>

<div class="profile-container">
    <div class="profile-grid">
        <div class="profile-sidebar">
            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-info">
                    <?php 
                    // Get profile image from user data or session
                    $profileImage = !empty($user['profile_image']) ? $user['profile_image'] : ($_SESSION['user_profile_image'] ?? null);
                    
                    // Debug image path
                    if (!empty($profileImage)) {
                        $imagePath = $profileImage;
                        $imageUrl = SITE_URL . '/' . $imagePath;
                        $timestamp = time(); // Add timestamp to prevent caching
                        $imageUrlWithCache = $imageUrl . '?t=' . $timestamp;
                        error_log("Image path in view: " . $imagePath);
                        error_log("Full image URL: " . $imageUrl);
                    ?>
                        <div class="profile-image-container">
                            <img src="<?= $imageUrlWithCache ?>" 
                                class="profile-image" 
                                onerror="this.onerror=null; this.src='<?= SITE_URL ?>/assets/images/placeholder.svg'; console.error('Failed to load profile image');">
                        </div>
                    <?php } else { ?>
                        <div class="profile-placeholder">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php } ?>
                    <h2 class="profile-name"><?= htmlspecialchars($user['name']) ?></h2>
                    <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                    <p class="profile-date">Member since: <?= date('M d, Y', strtotime($user['created_at'])) ?></p>
                </div>
            </div>
            
            <!-- User Stats Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h3>Your Activity</h3>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stats-item">
                            <div class="stats-number"><?= $stats['post_count'] ?></div>
                            <div class="stats-label">Posts</div>
                        </div>
                        <div class="stats-item">
                            <div class="stats-number"><?= $stats['comment_count'] ?></div>
                            <div class="stats-label">Comments</div>
                        </div>
                        <div class="stats-item">
                            <div class="stats-number"><?= $stats['like_count'] ?></div>
                            <div class="stats-label">Likes</div>
                        </div>
                        <div class="stats-item">
                            <div class="stats-number"><?= $stats['photo_count'] ?? 0 ?></div>
                            <div class="stats-label">Photos</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="profile-main">
            <!-- Flash Messages -->
            <div id="successAlert" class="alert alert-success">
                Profile updated successfully!
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
            
            <div id="errorAlert" class="alert alert-danger">
                <span id="errorMessage"></span>
                <button type="button" class="alert-close" aria-label="Close">&times;</button>
            </div>
            
            <?php if ($flashMessage = getFlashMessage('success')): ?>
                <div class="alert alert-success show">
                    <?= $flashMessage ?>
                    <button type="button" class="alert-close" aria-label="Close">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if ($flashMessage = getFlashMessage('danger')): ?>
                <div class="alert alert-danger show">
                    <?= $flashMessage ?>
                    <button type="button" class="alert-close" aria-label="Close">&times;</button>
                </div>
            <?php endif; ?>
            
            <!-- Edit Profile Card -->
            <div class="profile-card">
                <div class="card-header">
                    <h3>Edit Profile</h3>
                    <?php if (isset($_GET['debug'])): ?>
                    <button type="button" class="btn btn-small btn-outline" onclick="debugUserData()">Debug Data</button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="?page=profile&action=update_profile" enctype="multipart/form-data" id="profileForm">
                        <div class="form-group">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="bio" class="form-label">Bio</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <div class="image-upload-area" id="dropZone">
                                <div class="file-input-container">
                                    <input type="file" class="file-input" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/gif">
                                    <label class="file-input-label" for="profile_image">Upload</label>
                                </div>
                                <div class="form-hint">Max file size: 5MB. Allowed types: jpg, jpeg, png, gif</div>
                                <div class="drag-text">
                                    <p>or drag and drop image here</p>
                                </div>
                                
                                <!-- Current profile image (if exists) -->
                                <?php 
                                // Get profile image from user data or session
                                $profileImage = !empty($user['profile_image']) ? $user['profile_image'] : ($_SESSION['user_profile_image'] ?? null);
                                
                                if (!empty($profileImage)) {
                                    $imagePath = $profileImage;
                                    $imageUrl = SITE_URL . '/' . $imagePath;
                                    $timestamp = time(); // Add timestamp to prevent caching
                                    $imageUrlWithCache = $imageUrl . '?t=' . $timestamp;
                                ?>
                                <div class="image-preview">
                                    <label class="preview-label">Current profile image:</label>
                                    <div>
                                        <img src="<?= $imageUrlWithCache ?>" 
                                             class="thumbnail" 
                                             alt="Current profile image"
                                             onerror="this.onerror=null; this.src='<?= SITE_URL ?>/assets/images/placeholder.svg'; console.error('Failed to load thumbnail');">
                                    </div>
                                </div>
                                <?php } ?>
                                
                                <!-- New image preview will be shown here by JavaScript -->
                                <div class="image-preview" id="newImagePreview" style="display: none;">
                                    <label class="preview-label">New profile image:</label>
                                    <div>
                                        <img id="previewImg" class="thumbnail">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="buttons-container">
                            <button type="submit" class="btn btn-primary" id="saveButton">
                                <i class="fas fa-save btn-icon"></i>Save Changes
                            </button>
                            <div class="spinner" id="saveSpinner"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview when selecting a file
document.addEventListener('DOMContentLoaded', function() {
    const profileImageInput = document.getElementById('profile_image');
    const avatarImage = document.querySelector('.profile-image-container img');
    const avatarPlaceholder = document.querySelector('.profile-placeholder');
    const profileForm = document.getElementById('profileForm');
    const saveButton = document.getElementById('saveButton');
    const saveSpinner = document.getElementById('saveSpinner');
    const dropZone = document.getElementById('dropZone');
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');
    const errorMessage = document.getElementById('errorMessage');
    
    // Close button for alerts
    const closeButtons = document.querySelectorAll('.alert-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            this.parentElement.style.display = 'none';
        });
    });
    
    // Form submission handler with AJAX
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Show spinner and disable button
            saveButton.disabled = true;
            saveSpinner.style.display = 'block';
            
            // Hide any existing alerts
            successAlert.style.display = 'none';
            errorAlert.style.display = 'none';
            
            // Create FormData object
            const formData = new FormData(profileForm);
            
            // Log form data for debugging (but exclude file binary)
            console.log('Submitting form with data:', {
                name: formData.get('name'),
                email: formData.get('email'),
                bio: formData.get('bio'),
                'profile_image_filename': formData.get('profile_image') ? formData.get('profile_image').name : 'No file'
            });
            
            // Send AJAX request
            fetch(profileForm.action, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                // Check if response is OK
                if (!response.ok) {
                    throw new Error(`Server returned ${response.status}: ${response.statusText}`);
                }
                
                // Check content type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error(`Expected JSON response but got ${contentType}`);
                }
                
                return response.json();
            })
            .then(data => {
                console.log('Received response:', data);
                
                // Handle successful response
                if (data.success) {
                    // Show success message
                    successAlert.style.display = 'block';
                    
                    // Update profile image if a new one was uploaded
                    if (data.profile_image) {
                        updateProfileImage(data.profile_image);
                        
                        // Also update the current image thumbnail
                        const currentImage = document.querySelector('.image-preview img');
                        if (currentImage) {
                            const imageUrl = '<?= SITE_URL ?>/' + data.profile_image + '?t=' + new Date().getTime();
                            currentImage.src = imageUrl;
                        }
                        
                        // Hide the new image preview
                        const newImagePreview = document.getElementById('newImagePreview');
                        if (newImagePreview) {
                            newImagePreview.style.display = 'none';
                        }
                    }
                    
                    // Update user name if changed
                    if (data.name) {
                        const nameElement = document.querySelector('.profile-name');
                        if (nameElement) {
                            nameElement.textContent = data.name;
                        }
                    }
                    
                    // Update user email if changed
                    if (data.email) {
                        const emailElement = document.querySelector('.profile-email');
                        if (emailElement) {
                            emailElement.textContent = data.email;
                        }
                    }
                    
                    // Reset the file input
                    profileImageInput.value = '';
                } else {
                    // Show error message
                    errorMessage.textContent = data.message || 'An error occurred while updating your profile.';
                    errorAlert.style.display = 'block';
                }
            })
            .catch(error => {
                // Handle error
                console.error('Error:', error);
                errorMessage.textContent = 'An error occurred while updating your profile. Please try again later.';
                errorAlert.style.display = 'block';
                
                // Log more detailed error information
                const errorDetails = document.createElement('div');
                errorDetails.className = 'error-details';
                errorDetails.textContent = `Error details: ${error.message}`;
                errorMessage.appendChild(errorDetails);
            })
            .finally(() => {
                // Re-enable button and hide spinner
                saveButton.disabled = false;
                saveSpinner.style.display = 'none';
            });
        });
    }
    
    // Function to update profile image
    function updateProfileImage(imagePath) {
        // Add cache-busting timestamp to prevent browser caching
        const timestamp = new Date().getTime();
        const imageUrl = '<?= SITE_URL ?>/' + imagePath + '?t=' + timestamp;
        
        // Try to get the profile image elements
        const avatarImage = document.querySelector('.profile-image-container img');
        const avatarPlaceholder = document.querySelector('.profile-placeholder');
        
        if (avatarImage) {
            // If we already have an image element, just update its src
            avatarImage.src = imageUrl;
            avatarImage.style.display = 'block';
            console.log('Updated existing avatar image to', imageUrl);
        } else if (avatarPlaceholder && avatarPlaceholder.parentNode) {
            // Replace the placeholder with an actual image
            console.log('Creating new avatar image element with', imageUrl);
            const img = document.createElement('img');
            img.src = imageUrl;
            img.className = 'profile-image';
            
            const parent = avatarPlaceholder.parentNode;
            if (parent) {
                try {
                    parent.replaceChild(img, avatarPlaceholder);
                } catch (err) {
                    console.error('Error replacing placeholder:', err);
                    // Try an alternative approach
                    parent.innerHTML = '';
                    parent.appendChild(img);
                }
            }
        } else {
            // If we can't find either element, try to update any profile image on the page
            console.log('Could not find avatar image or placeholder, trying alternative approach');
            const profileContainer = document.querySelector('.profile-info');
            if (profileContainer) {
                // Clear existing content and add the new image
                const img = document.createElement('img');
                img.src = imageUrl;
                img.className = 'profile-image';
                
                // Find any existing image or placeholder and replace it, or just append
                const existingImage = profileContainer.querySelector('img, .profile-placeholder');
                if (existingImage) {
                    existingImage.replaceWith(img);
                } else {
                    profileContainer.prepend(img);
                }
            } else {
                console.error('Could not update profile image, consider refreshing the page');
            }
        }
    }
    
    // Drag and drop functionality
    if (dropZone && profileImageInput) {
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });
        
        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        // Handle dropped files
        dropZone.addEventListener('drop', handleDrop, false);
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        function highlight(e) {
            dropZone.classList.add('highlight');
        }
        
        function unhighlight(e) {
            dropZone.classList.remove('highlight');
        }
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length) {
                profileImageInput.files = files;
                // Trigger change event to update preview
                const event = new Event('change', { bubbles: true });
                profileImageInput.dispatchEvent(event);
            }
        }
    }
    
    if (profileImageInput) {
        profileImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    // Get updated references to the DOM elements
                    const avatarImage = document.querySelector('.profile-image-container img');
                    const avatarPlaceholder = document.querySelector('.profile-placeholder');
                    
                    // Update only the main avatar at the top of the page
                    if (avatarImage) {
                        // If we already have an image, just update its src
                        avatarImage.src = e.target.result;
                        avatarImage.style.display = 'block';
                        console.log('Updated existing avatar image');
                    } else if (avatarPlaceholder && avatarPlaceholder.parentNode) {
                        // Replace the placeholder with an actual image
                        console.log('Creating new avatar image element');
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'profile-image';
                        
                        try {
                            const parent = avatarPlaceholder.parentNode;
                            parent.replaceChild(img, avatarPlaceholder);
                        } catch (err) {
                            console.error('Error updating image preview:', err);
                        }
                    } else {
                        console.log('Could not find avatar elements for preview');
                        // Try alternative approach
                        const profileContainer = document.querySelector('.profile-info');
                        if (profileContainer) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'profile-image';
                            
                            const existingImage = profileContainer.querySelector('img, .profile-placeholder');
                            if (existingImage) {
                                existingImage.replaceWith(img);
                            } else {
                                profileContainer.prepend(img);
                            }
                        }
                    }
                    
                    // Update the image preview in the form
                    const newImagePreview = document.getElementById('newImagePreview');
                    const previewImg = document.getElementById('previewImg');
                    if (newImagePreview && previewImg) {
                        previewImg.src = e.target.result;
                        newImagePreview.style.display = 'block';
                        console.log('Updated preview image in form');
                    } else {
                        console.error('Could not find preview image container or image element');
                    }
                };
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});

// Debug user data
function debugUserData() {
    console.log('User data debug:');
    <?php if (!empty($user)): ?>
    console.log('User data:', <?= json_encode($user) ?>);
    <?php else: ?>
    console.log('No user data available');
    <?php endif; ?>
    
    console.log('Session data:');
    console.log('user_id:', '<?= $_SESSION['user_id'] ?? "not set" ?>');
    console.log('user_name:', '<?= $_SESSION['user_name'] ?? "not set" ?>');
    console.log('user_profile_image:', '<?= $_SESSION['user_profile_image'] ?? "not set" ?>');
}

// Auto-run debug if debug parameter is set
<?php if (isset($_GET['debug'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    debugUserData();
});
<?php endif; ?>
</script>

<?php if (isset($_GET['debug'])): ?>
<div class="debug-info">
    <h4>Debug Information</h4>
    <div class="debug-grid">
        <div class="debug-section">
            <h5>User Data</h5>
            <pre><?php print_r($user); ?></pre>
        </div>
        <div class="debug-section">
            <h5>Session Data</h5>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>
    </div>
    <div class="debug-section">
        <h5>Current Profile Image</h5>
        <p>Path: <?= $user['profile_image'] ?? 'Not set' ?></p>
        <p>Session Path: <?= $_SESSION['user_profile_image'] ?? 'Not set' ?></p>
        <p>Full URL: <?= $imageUrl ?? 'Not set' ?></p>
    </div>
</div>
<?php endif; ?>

<?php
require_once 'includes/footer.php';
?> 