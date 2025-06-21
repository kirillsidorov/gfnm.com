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
                    <h1 class="display-4 text-center mb-4 text-primary">Privacy Policy</h1>
                    <p class="text-center text-muted mb-5">
                        <strong>Last updated:</strong> <?= date('F j, Y') ?>
                    </p>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">1. Introduction</h2>
                        <p>
                            Welcome to Georgian Food Near Me ("we," "our," or "us"). We are committed to protecting your privacy and personal information. 
                            This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website 
                            georgianfoodnearme.com (the "Site") and use our services.
                        </p>
                        <p>
                            By using our Site, you consent to the data practices described in this Privacy Policy. 
                            If you do not agree with the practices described in this policy, please do not use our Site.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">2. Information We Collect</h2>
                        
                        <h3 class="h5 text-secondary mb-3">2.1 Personal Information</h3>
                        <p>We may collect personal information that you voluntarily provide, including:</p>
                        <ul class="mb-4">
                            <li>Contact information (name, email address, phone number)</li>
                            <li>Location data when you search for restaurants near you</li>
                            <li>Review and rating content you submit</li>
                            <li>Account information if you create an account</li>
                            <li>Communication preferences</li>
                        </ul>

                        <h3 class="h5 text-secondary mb-3">2.2 Automatically Collected Information</h3>
                        <p>When you visit our Site, we automatically collect certain information:</p>
                        <ul class="mb-4">
                            <li>IP address and device information</li>
                            <li>Browser type and version</li>
                            <li>Pages visited and time spent on our Site</li>
                            <li>Referring website information</li>
                            <li>Search queries and interactions with our Site</li>
                        </ul>

                        <h3 class="h5 text-secondary mb-3">2.3 Location Information</h3>
                        <p>
                            With your permission, we may collect and use your location information to provide 
                            location-based services, such as showing Georgian restaurants near you. You can 
                            disable location services through your browser or device settings.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">3. How We Use Your Information</h2>
                        <p>We use the collected information for the following purposes:</p>
                        <ul>
                            <li><strong>Service Provision:</strong> To provide, maintain, and improve our restaurant directory services</li>
                            <li><strong>Location Services:</strong> To show you Georgian restaurants near your location</li>
                            <li><strong>Communication:</strong> To respond to your inquiries and provide customer support</li>
                            <li><strong>Personalization:</strong> To customize your experience and provide relevant recommendations</li>
                            <li><strong>Analytics:</strong> To analyze usage patterns and improve our Site functionality</li>
                            <li><strong>Legal Compliance:</strong> To comply with legal obligations and protect our rights</li>
                        </ul>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">4. Google Maps Integration</h2>
                        <div class="bg-light p-4 rounded">
                            <h3 class="h5 text-secondary mb-3">Google Maps API and Place ID</h3>
                            <p>
                                Our Site uses Google Maps API and Google Places API to provide location-based services. 
                                When you use these features:
                            </p>
                            <ul class="mb-3">
                                <li>Your location data may be shared with Google according to Google's Privacy Policy</li>
                                <li>We use Google Place IDs to identify and display restaurant information</li>
                                <li>Map interactions are subject to Google's Terms of Service</li>
                                <li>Google may collect and use your data according to their privacy practices</li>
                            </ul>
                            <p class="mb-0">
                                <strong>Important:</strong> Please review 
                                <a href="https://policies.google.com/privacy" target="_blank" class="text-primary">Google's Privacy Policy</a> 
                                to understand how Google handles your data when using their mapping services.
                            </p>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">5. Information Sharing and Disclosure</h2>
                        <p>We do not sell, trade, or rent your personal information to third parties. We may share your information in the following circumstances:</p>
                        
                        <h3 class="h5 text-secondary mb-3">5.1 Service Providers</h3>
                        <ul class="mb-4">
                            <li>Google (for Maps and Places API services)</li>
                            <li>Web hosting and technical service providers</li>
                            <li>Analytics providers (if applicable)</li>
                        </ul>

                        <h3 class="h5 text-secondary mb-3">5.2 Legal Requirements</h3>
                        <p>We may disclose your information if required by law or to:</p>
                        <ul>
                            <li>Comply with legal processes or government requests</li>
                            <li>Protect our rights, property, or safety</li>
                            <li>Protect the rights, property, or safety of our users</li>
                            <li>Investigate and prevent fraud or security issues</li>
                        </ul>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">6. Data Security</h2>
                        <p>
                            We implement appropriate technical and organizational measures to protect your personal information 
                            against unauthorized access, alteration, disclosure, or destruction. However, no method of transmission 
                            over the internet or electronic storage is 100% secure.
                        </p>
                        <div class="bg-warning bg-opacity-10 border border-warning p-3 rounded">
                            <strong>Note:</strong> While we strive to protect your personal information, we cannot guarantee 
                            absolute security. Please use our Site at your own risk.
                        </div>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">7. Cookies and Tracking Technologies</h2>
                        <p>We use cookies and similar tracking technologies to:</p>
                        <ul>
                            <li>Remember your preferences and settings</li>
                            <li>Analyze Site usage and performance</li>
                            <li>Provide personalized content and recommendations</li>
                            <li>Enable certain Site functionalities</li>
                        </ul>
                        <p>
                            You can control cookies through your browser settings. However, disabling cookies may 
                            affect the functionality of our Site.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">8. Your Rights and Choices</h2>
                        <p>Depending on your location, you may have the following rights regarding your personal information:</p>
                        <ul>
                            <li><strong>Access:</strong> Request access to your personal information</li>
                            <li><strong>Correction:</strong> Request correction of inaccurate information</li>
                            <li><strong>Deletion:</strong> Request deletion of your personal information</li>
                            <li><strong>Portability:</strong> Request a copy of your data in a portable format</li>
                            <li><strong>Objection:</strong> Object to certain processing of your information</li>
                            <li><strong>Withdrawal:</strong> Withdraw consent where processing is based on consent</li>
                        </ul>
                        <p>
                            To exercise these rights, please contact us at 
                            <a href="mailto:privacy@georgianfoodnearme.com" class="text-primary">privacy@georgianfoodnearme.com</a>.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">9. Children's Privacy</h2>
                        <p>
                            Our Site is not intended for children under 13 years of age. We do not knowingly collect 
                            personal information from children under 13. If we become aware that we have collected 
                            personal information from a child under 13, we will take steps to delete such information.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">10. International Data Transfers</h2>
                        <p>
                            Your information may be transferred to and processed in countries other than your country of residence. 
                            These countries may have different data protection laws. By using our Site, you consent to such transfers.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">11. Changes to This Privacy Policy</h2>
                        <p>
                            We may update this Privacy Policy from time to time. We will notify you of any material changes 
                            by posting the new Privacy Policy on this page and updating the "Last updated" date.
                        </p>
                        <p>
                            Your continued use of our Site after any changes indicates your acceptance of the updated Privacy Policy.
                        </p>
                    </div>

                    <div class="mb-5">
                        <h2 class="h3 text-primary mb-3">12. Contact Us</h2>
                        <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
                        <div class="bg-light p-4 rounded">
                            <ul class="list-unstyled mb-0">
                                <li><strong>Email:</strong> <a href="mailto:info@georgianfoodnearme.com" class="text-primary">info@georgianfoodnearme.com</a></li>
                                <li><strong>Website:</strong> <a href="<?= base_url() ?>" class="text-primary">georgianfoodnearme.com</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="<?= base_url() ?>" class="btn btn-primary">
                            <i class="fas fa-home me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>