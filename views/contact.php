<?php require_once 'includes/header.php'; ?>

<div class="custom-contact-page">
    <div class="contact-page-bg"></div>
    
    <div class="contact-page-container">
        <section class="contact-form-section">
            <div class="section-header">
                <h1>Contact Us</h1>
                <div class="section-divider"></div>
            </div>
            
            <div class="contact-intro">
                <p>Have a question, feedback, or just want to say hello? We'd love to hear from you! Fill out the form below and we'll get back to you as soon as possible.</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="error-alert">
                    <h5 class="error-heading">Please fix the following errors:</h5>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form action="<?= SITE_URL ?>/?page=contact" method="post" id="contactForm" class="custom-contact-form">
                <div class="form-group">
                    <label for="name">Your Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject <span class="required">*</span></label>
                    <input type="text" id="subject" name="subject" value="<?= isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : '' ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Message <span class="required">*</span></label>
                    <textarea id="message" name="message" rows="5" required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                </div>
                
                <div class="form-submit">
                    <button type="submit" class="submit-btn">
                        <span class="btn-text">Send Message</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </section>
        
        <section class="other-contact-section">
            <div class="section-header">
                <h2>Other Ways to Reach Us</h2>
                <div class="section-divider"></div>
            </div>
            
            <div class="contact-options">
                <div class="contact-option">
                    <div class="option-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Email</h3>
                    <p><a href="mailto:<?= ADMIN_EMAIL ?>" class="custom-link"><?= ADMIN_EMAIL ?></a></p>
                </div>
                
                <div class="contact-option">
                    <div class="option-icon">
                        <i class="fas fa-share-alt"></i>
                    </div>
                    <h3>Follow Us</h3>
                    <div class="social-links contact-socials">
                        <a href="#" class="social-link facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="#" class="social-link twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link linkedin">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 