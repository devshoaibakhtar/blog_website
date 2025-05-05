    </div>

    <footer class="custom-footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3><?= SITE_NAME ?></h3>
                    <p>Empowering writers to share their voice with the world</p>
                </div>
                <div class="footer-copyright">
                    &copy; <?= date('Y') ?> <?= SITE_NAME ?>. All rights reserved.
                </div>
                <div class="footer-links">
                    <a href="<?= SITE_URL ?>/?page=terms" class="footer-link">Terms of Service</a>
                    <a href="<?= SITE_URL ?>/?page=privacy" class="footer-link">Privacy Policy</a>
                    <a href="<?= SITE_URL ?>/?page=contact" class="footer-link">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Global JavaScript variables
        const siteUrl = '<?= SITE_URL ?>';

        // Fix for image loading errors
        document.addEventListener('DOMContentLoaded', function() {
            // Handle image errors for all images in the page
            document.querySelectorAll('img').forEach(function(img) {
                img.addEventListener('error', function() {
                    // Set default image if loading fails
                    if (this.src !== siteUrl + '/assets/images/placeholder.svg') {
                        console.log('Image failed to load: ' + this.src);
                        this.src = siteUrl + '/assets/images/placeholder.svg';
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.3/dist/jquery.min.js"></script>
    <script src="<?= SITE_URL ?>/assets/js/fixed_script.js"></script>
</body>
</html> 