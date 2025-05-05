<?php require_once 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Create New Post</h1>
            <a href="<?= SITE_URL ?>/?page=posts" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Posts
            </a>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <form action="<?= SITE_URL ?>/?page=create-post" method="post" enctype="multipart/form-data" id="postForm">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="taxonomy_id" class="form-label">Category</label>
                        <select class="form-select" id="taxonomy_id" name="taxonomy_id" required>
                            <option value="">Select a category</option>
                            <?php foreach ($taxonomies as $taxonomy): ?>
                                <option value="<?= $taxonomy['id'] ?>"><?= $taxonomy['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="tags-input" class="form-label">Tags</label>
                        
                        <!-- Hidden fields to store our selected and new tags -->
                        <input type="hidden" id="new-tags-json" name="new_tags" value="[]">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <select class="form-select mb-2" id="existing-tags" multiple>
                                    <option value="" disabled>Select from existing tags or add your own</option>
                                    <?php foreach ($tags as $tag): ?>
                                        <option value="<?= $tag['id'] ?>"><?= $tag['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="button" id="add-existing-tag" class="btn btn-outline-secondary">
                                    <i class="fas fa-plus me-1"></i>Add Selected Tags
                                </button>
                            </div>
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" id="new-tag-input" placeholder="Type a new tag">
                                    <button class="btn btn-outline-primary" type="button" id="add-new-tag">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Press Enter to add multiple tags</small>
                            </div>
                        </div>
                        
                        <!-- Tag display area -->
                        <div id="tags-container" class="mt-3 d-flex flex-wrap gap-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="featured_image" class="form-label">Featured Image</label>
                        <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="10" required></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" name="save_draft" value="1" class="btn btn-secondary me-2">Save as Draft</button>
                        <button type="submit" class="btn btn-primary">Submit for Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.tag-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    background-color: #f0f0f0;
    border-radius: 30px;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
    transition: all 0.2s;
}
.tag-badge.existing {
    background-color: #e9ecef;
    border: 1px solid #dee2e6;
}
.tag-badge.new {
    background-color: #d1e7dd;
    border: 1px solid #badbcc;
}
.tag-badge .remove-tag {
    margin-left: 5px;
    cursor: pointer;
    color: #6c757d;
}
.tag-badge .remove-tag:hover {
    color: #dc3545;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const existingTagsSelect = document.getElementById('existing-tags');
    const addExistingTagBtn = document.getElementById('add-existing-tag');
    const newTagInput = document.getElementById('new-tag-input');
    const addNewTagBtn = document.getElementById('add-new-tag');
    const tagsContainer = document.getElementById('tags-container');
    const newTagsJson = document.getElementById('new-tags-json');
    
    // Store selected and new tags
    const selectedTags = [];
    const newTags = [];
    
    // Add existing tags when button is clicked
    addExistingTagBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const selectedOptions = [...existingTagsSelect.selectedOptions];
        
        selectedOptions.forEach(option => {
            // Only add if not already selected
            if (selectedTags.findIndex(tag => tag.id === option.value) === -1) {
                selectedTags.push({
                    id: option.value,
                    name: option.text
                });
                
                // Create a hidden input for the tag ID
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'tags[]';
                hiddenInput.value = option.value;
                document.getElementById('postForm').appendChild(hiddenInput);
                
                renderTags();
            }
        });
        
        // Clear selection
        existingTagsSelect.selectedIndex = -1;
    });
    
    // Add new tag
    function addNewTag() {
        const tagName = newTagInput.value.trim();
        
        if (tagName) {
            // Check if this tag already exists in existing tags
            const existingTag = [...existingTagsSelect.options].find(option => 
                option.text.toLowerCase() === tagName.toLowerCase()
            );
            
            if (existingTag) {
                // Add as existing tag instead
                if (selectedTags.findIndex(tag => tag.id === existingTag.value) === -1) {
                    selectedTags.push({
                        id: existingTag.value,
                        name: existingTag.text
                    });
                    
                    // Create a hidden input for the tag ID
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'tags[]';
                    hiddenInput.value = existingTag.value;
                    document.getElementById('postForm').appendChild(hiddenInput);
                }
            } else {
                // Add as new tag
                // Check if already in new tags array
                if (newTags.indexOf(tagName) === -1) {
                    newTags.push(tagName);
                    updateNewTagsJson();
                }
            }
            
            // Clear input and render
            newTagInput.value = '';
            renderTags();
        }
    }
    
    // Add new tag when button is clicked
    addNewTagBtn.addEventListener('click', function(e) {
        e.preventDefault();
        addNewTag();
    });
    
    // Add new tag when Enter is pressed
    newTagInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addNewTag();
        }
    });
    
    // Update the JSON field with new tags
    function updateNewTagsJson() {
        newTagsJson.value = JSON.stringify(newTags);
    }
    
    // Render tags in the container
    function renderTags() {
        tagsContainer.innerHTML = '';
        
        // Render selected existing tags
        selectedTags.forEach(tag => {
            const tagBadge = document.createElement('div');
            tagBadge.className = 'tag-badge existing';
            tagBadge.dataset.id = tag.id;
            tagBadge.innerHTML = `
                ${tag.name}
                <span class="remove-tag" data-type="existing" data-id="${tag.id}">
                    <i class="fas fa-times"></i>
                </span>
            `;
            tagsContainer.appendChild(tagBadge);
        });
        
        // Render new tags
        newTags.forEach((tagName, index) => {
            const tagBadge = document.createElement('div');
            tagBadge.className = 'tag-badge new';
            tagBadge.innerHTML = `
                ${tagName}
                <span class="remove-tag" data-type="new" data-index="${index}">
                    <i class="fas fa-times"></i>
                </span>
            `;
            tagsContainer.appendChild(tagBadge);
        });
    }
    
    // Handle tag removal
    tagsContainer.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-tag');
        
        if (removeBtn) {
            const type = removeBtn.dataset.type;
            
            if (type === 'existing') {
                const tagId = removeBtn.dataset.id;
                // Remove from selected tags
                const index = selectedTags.findIndex(tag => tag.id === tagId);
                if (index !== -1) {
                    selectedTags.splice(index, 1);
                }
                
                // Remove hidden input
                const hiddenInput = document.querySelector(`input[name="tags[]"][value="${tagId}"]`);
                if (hiddenInput) {
                    hiddenInput.remove();
                }
            } else if (type === 'new') {
                const index = parseInt(removeBtn.dataset.index);
                // Remove from new tags
                newTags.splice(index, 1);
                updateNewTagsJson();
            }
            
            renderTags();
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 