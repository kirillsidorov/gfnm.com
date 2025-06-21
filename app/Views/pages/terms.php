<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<meta name="description" content="<?= esc($meta_description) ?>">
<link rel="canonical" href="<?= esc($canonical_url) ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h1 class="display-4 text-center mb-4 text-primary">Terms of Service</h1>
                    <p class="text-center text-muted mb-5">
                        <strong>Last updated:</strong> <?= date('F j, Y') ?>
                    </p>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">1. Acceptance of Terms</h2>
                        <p>
                            Welcome to Georgian Food Near Me ("we," "our," or "us"). These Terms of Service ("Terms") 
                            govern your use of our website georgianfoodnearme.com (the "Site") and related services 
                            (collectively, the "Service").
                        </p>
                        <p>
                            By accessing or using our Service, you agree to be bound by these Terms. If you disagree 
                            with any part of these Terms, then you may not access the Service.
                        </p>
                        <div class="bg-info bg-opacity-10 border border-info p-3 rounded">
                            <strong>Important:</strong> Please read these Terms carefully before using our Service. 
                            Your use of the Service constitutes acceptance of these Terms.
                        </div>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">2. Description of Service</h2>
                        <p>
                            Georgian Food Near Me is a directory platform that helps users discover Georgian restaurants 
                            and culinary experiences. Our Service includes:
                        </p>
                        <ul>
                            <li>Restaurant directory and search functionality</li>
                            <li>Location-based restaurant recommendations</li>
                            <li>Restaurant information, reviews, and ratings</li>
                            <li>Integration with Google Maps and Places API</li>
                            <li>User-generated content features</li>
                            <li>Administrative tools for restaurant owners</li>
                        </ul>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">3. User Accounts and Registration</h2>
                        
                        <h3 class="h5 text-secondary mb-3">3.1 Account Creation</h3>
                        <p>
                            To access certain features of our Service, you may be required to register for an account. 
                            You agree to provide accurate, current, and complete information during registration.
                        </p>

                        <h3 class="h5 text-secondary mb-3">3.2 Account Security</h3>
                        <p>You are responsible for:</p>
                        <ul>
                            <li>Maintaining the confidentiality of your account credentials</li>
                            <li>All activities that occur under your account</li>
                            <li>Notifying us immediately of any unauthorized access</li>
                            <li>Ensuring your account information remains accurate and up-to-date</li>
                        </ul>

                        <h3 class="h5 text-secondary mb-3">3.3 Account Termination</h3>
                        <p>
                            We reserve the right to suspend or terminate your account at any time for violations 
                            of these Terms or for any other reason at our discretion.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">4. User Conduct and Prohibited Activities</h2>
                        
                        <h3 class="h5 text-secondary mb-3">4.1 Acceptable Use</h3>
                        <p>You agree to use our Service only for lawful purposes and in accordance with these Terms.</p>

                        <h3 class="h5 text-secondary mb-3">4.2 Prohibited Activities</h3>
                        <p>You may not:</p>
                        <ul>
                            <li>Post false, misleading, or fraudulent restaurant information</li>
                            <li>Submit fake reviews or ratings</li>
                            <li>Harass, abuse, or harm other users</li>
                            <li>Violate any laws or regulations</li>
                            <li>Infringe on intellectual property rights</li>
                            <li>Upload malicious code or viruses</li>
                            <li>Attempt to gain unauthorized access to our systems</li>
                            <li>Use automated tools to access or scrape our Site</li>
                            <li>Impersonate others or create fake accounts</li>
                            <li>Spam or send unsolicited communications</li>
                        </ul>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">5. User-Generated Content</h2>
                        
                        <h3 class="h5 text-secondary mb-3">5.1 Content Ownership</h3>
                        <p>
                            You retain ownership of content you submit to our Service, including reviews, ratings, 
                            photos, and comments ("User Content").
                        </p>

                        <h3 class="h5 text-secondary mb-3">5.2 License to Use</h3>
                        <p>
                            By submitting User Content, you grant us a worldwide, non-exclusive, royalty-free license to:
                        </p>
                        <ul>
                            <li>Display, reproduce, and distribute your content on our platform</li>
                            <li>Modify and adapt content for technical and editorial purposes</li>
                            <li>Use content for promotional and marketing purposes</li>
                        </ul>

                        <h3 class="h5 text-secondary mb-3">5.3 Content Standards</h3>
                        <p>All User Content must:</p>
                        <ul>
                            <li>Be accurate and truthful</li>
                            <li>Not violate any laws or third-party rights</li>
                            <li>Not contain harmful, offensive, or inappropriate material</li>
                            <li>Be relevant to Georgian cuisine and restaurants</li>
                        </ul>

                        <h3 class="h5 text-secondary mb-3">5.4 Content Moderation</h3>
                        <p>
                            We reserve the right to review, edit, or remove any User Content that violates these Terms 
                            or our community guidelines.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">6. Google Maps and Third-Party Services</h2>
                        <div class="bg-light p-4 rounded">
                            <h3 class="h5 text-secondary mb-3">6.1 Google Services Integration</h3>
                            <p>Our Service integrates with Google Maps API and Google Places API. Your use of these features is subject to:</p>
                            <ul class="mb-3">
                                <li><a href="https://developers.google.com/maps/terms" target="_blank" class="text-primary">Google Maps/Google Earth Additional Terms of Service</a></li>
                                <li><a href="https://policies.google.com/privacy" target="_blank" class="text-primary">Google Privacy Policy</a></li>
                                <li><a href="https://policies.google.com/terms" target="_blank" class="text-primary">Google Terms of Service</a></li>
                            </ul>

                            <h3 class="h5 text-secondary mb-3">6.2 Third-Party Content</h3>
                            <p class="mb-0">
                                We are not responsible for the accuracy or availability of third-party services or content. 
                                Use of third-party services is at your own risk.
                            </p>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">7. Restaurant Owners and Business Listings</h2>
                        
                        <h3 class="h5 text-secondary mb-3">7.1 Business Information</h3>
                        <p>
                            Restaurant owners may claim and manage their business listings. You represent that you have 
                            the authority to manage the business information you submit.
                        </p>

                        <h3 class="h5 text-secondary mb-3">7.2 Accuracy of Information</h3>
                        <p>Business owners are responsible for:</p>
                        <ul>
                            <li>Maintaining accurate business information</li>
                            <li>Updating hours, contact details, and menu information</li>
                            <li>Responding to customer inquiries appropriately</li>
                            <li>Complying with all applicable laws and regulations</li>
                        </ul>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">8. Intellectual Property Rights</h2>
                        
                        <h3 class="h5 text-secondary mb-3">8.1 Our Intellectual Property</h3>
                        <p>
                            The Service, including its design, functionality, and content (excluding User Content), 
                            is owned by us and protected by intellectual property laws.
                        </p>

                        <h3 class="h5 text-secondary mb-3">8.2 Trademark Rights</h3>
                        <p>
                            "Georgian Food Near Me" and related marks are our trademarks. You may not use our 
                            trademarks without prior written permission.
                        </p>

                        <h3 class="h5 text-secondary mb-3">8.3 Copyright Infringement</h3>
                        <p>
                            If you believe your copyright has been infringed, please contact us at 
                            <a href="mailto:info@georgianfoodnearme.com" class="text-primary">info@georgianfoodnearme.com</a> 
                            with details of the alleged infringement.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">9. Privacy and Data Protection</h2>
                        <p>
                            Your privacy is important to us. Our collection and use of personal information is governed 
                            by our <a href="<?= base_url('privacy') ?>" class="text-primary">Privacy Policy</a>, 
                            which is incorporated into these Terms by reference.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">10. Disclaimers and Limitations of Liability</h2>
                        
                        <h3 class="h5 text-secondary mb-3">10.1 Service Disclaimers</h3>
                        <div class="bg-warning bg-opacity-10 border border-warning p-3 rounded mb-3">
                            <p class="mb-2"><strong>IMPORTANT DISCLAIMERS:</strong></p>
                            <ul class="mb-0">
                                <li>Our Service is provided "as is" without warranties of any kind</li>
                                <li>We do not guarantee the accuracy of restaurant information</li>
                                <li>We are not responsible for the quality or safety of restaurants listed</li>
                                <li>User reviews and ratings reflect individual opinions</li>
                            </ul>
                        </div>

                        <h3 class="h5 text-secondary mb-3">10.2 Limitation of Liability</h3>
                        <p>
                            To the maximum extent permitted by law, we shall not be liable for any indirect, 
                            incidental, special, or consequential damages arising from your use of our Service.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">11. Indemnification</h2>
                        <p>
                            You agree to indemnify and hold us harmless from any claims, damages, or expenses 
                            arising from your use of the Service, violation of these Terms, or infringement 
                            of any third-party rights.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">12. Termination</h2>
                        <p>
                            We may terminate or suspend your access to our Service immediately, without prior 
                            notice, for any reason, including breach of these Terms.
                        </p>
                        <p>
                            Upon termination, your right to use the Service will cease, but provisions regarding 
                            intellectual property, disclaimers, and limitations of liability will survive.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">13. Changes to Terms</h2>
                        <p>
                            We reserve the right to modify these Terms at any time. We will notify users of 
                            material changes by posting the updated Terms on our Site and updating the 
                            "Last updated" date.
                        </p>
                        <p>
                            Your continued use of the Service after changes constitutes acceptance of the new Terms.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">14. Governing Law and Jurisdiction</h2>
                        <p>
                            These Terms are governed by and construed in accordance with applicable laws. 
                            Any disputes arising from these Terms or your use of the Service will be resolved 
                            through appropriate legal channels.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">15. Severability</h2>
                        <p>
                            If any provision of these Terms is found to be unenforceable, the remaining 
                            provisions will remain in full force and effect.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">16. Contact Information</h2>
                        <p>If you have questions about these Terms, please contact us:</p>
                        <div class="bg-light p-4 rounded">
                            <ul class="list-unstyled mb-0">
                                <li><strong>General:</strong> <a href="mailto:info@georgianfoodnearme.com" class="text-primary">info@georgianfoodnearme.com</a></li>
                                <li><strong>Website:</strong> <a href="<?= base_url() ?>" class="text-primary">georgianfoodnearme.com</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="text-center">
                        <div class="mb-3">
                            <a href="<?= base_url('privacy') ?>" class="btn btn-outline-primary me-3">
                                <i class="fas fa-shield-alt me-2"></i>Privacy Policy
                            </a>
                            <a href="<?= base_url() ?>" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>