<?php require_once 'includes/header.php'; ?>

<div class="custom-about-page">
    <div class="about-page-bg"></div>
    
    <div class="about-page-container">
        <section class="about-us-section">
            <div class="section-header">
                <h1>About Us</h1>
                <div class="section-divider"></div>
            </div>
            
            <div class="about-content-wrapper">
                <div class="about-text-section">
                    <h2>Welcome to <?= SITE_NAME ?></h2>
                    <p>Welcome to WriteWise! We are dedicated to providing a platform for writers, bloggers, and content creators to share their thoughts, ideas, and experiences with the world.</p>
                    <p>Our mission is to foster a community of engaged writers and readers who can connect through meaningful content.</p>
                </div>
                
                <div class="about-text-section">
                    <h3>Our Story</h3>
                    <p>Founded in 2025, <?= SITE_NAME ?> started as a simple idea: create a platform where content creators can easily publish and manage their blog posts, while readers can discover high-quality content on topics they care about.</p>
                    <p>As we've grown, we've remained committed to our core values of creativity, community, and quality.</p>
                </div>
                
                <div class="about-text-section">
                    <h3>Our Features</h3>
                    <ul class="features-list">
                        <li><span>Easy Content Publishing:</span> Create, edit, and manage blog posts with our user-friendly interface.</li>
                        <li><span>Categories and Tags:</span> Organize your content to help readers find what they're looking for.</li>
                        <li><span>Social Sharing:</span> Share your posts across multiple platforms with a single click.</li>
                        <li><span>User Comments:</span> Engage with your readers through our comments section.</li>
                        <li><span>Responsive Design:</span> Our platform works beautifully on desktop, tablet, and mobile.</li>
                    </ul>
                </div>
                
                <div class="about-text-section">
                    <h3>Join Our Community</h3>
                    <p>We believe in the power of words to inspire, educate, and connect people. Whether you're a seasoned writer or just starting your blogging journey, we welcome you to join our community.</p>
                    <p>Ready to start sharing your stories? <a href="<?= SITE_URL ?>/?page=register" class="custom-link">Sign up today</a> or <a href="<?= SITE_URL ?>/?page=contact" class="custom-link">contact us</a> if you have any questions!</p>
                </div>
            </div>
        </section>
        
        <!-- Creator Section -->
        <section class="creator-section">
            <div class="section-header">
                <h2>Meet the Creator</h2>
                <div class="section-divider"></div>
            </div>
            
            <div class="creator-content">
                <div class="creator-profile">
                    <div class="creator-image-container">
                        <img src="<?= SITE_URL ?>/assets/images/creator.svg" alt="Creator" class="creator-image">
                    </div>
                    <h3>Shoaib</h3>
                    <p class="creator-title">Founder WriteWise</p>
                    <div class="social-links">
                        <a href="https://twitter.com/" target="_blank" class="social-link twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://linkedin.com/" target="_blank" class="social-link linkedin">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="https://github.com/" target="_blank" class="social-link github">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>
                </div>
                
                <div class="creator-bio">
                    <div class="bio-section">
                        <h4>Our Vision</h4>
                        <div class="section-divider small"></div>
                        <p>"I believe that everyone has a unique story to tell and valuable knowledge to share. My vision for <?= SITE_NAME ?> is to create a platform that empowers people to express themselves, connect with like-minded individuals, and build a community around shared interests and passions."</p>
                        <p>"In a world where digital content is increasingly dominated by algorithms and clickbait, we're committed to fostering authentic voices and meaningful conversations. Our platform is designed to put creators first, giving them the tools they need to succeed while maintaining full control over their content."</p>
                    </div>
                    
                    <div class="bio-section">
                        <h4>Background</h4>
                        <div class="section-divider small"></div>
                        <p>With over 1+ years of experience in web development and a passion for content creation, John founded <?= SITE_NAME ?> after experiencing firsthand the challenges that bloggers face with existing platforms.</p>
                        <p>His background in computer science and digital marketing has helped shape <?= SITE_NAME ?> into a platform that combines technical excellence with a deep understanding of content creators' needs.</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 