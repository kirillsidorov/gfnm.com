<?= $this->extend('layouts/main') ?>

<?= $this->section('head') ?>
<meta name="description" content="<?= esc($meta_description) ?>">
<link rel="canonical" href="<?= esc($canonical_url) ?>">
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-danger bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-bug fa-2x text-danger"></i>
                        </div>
                        <h1 class="display-5 text-danger mb-2">🐛 Found a Bug?</h1>
                        <p class="lead text-muted">
                            Help us improve Georgian Food Near Me by reporting any issues you encounter
                        </p>
                    </div>

                    <?php if (session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= session('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= session('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('bug-report/submit') ?>" method="post" id="bugReportForm">
                        <?= csrf_field() ?>
                        
                        <div class="mb-4">
                            <label for="bug_description" class="form-label">
                                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                <strong>What's wrong? *</strong>
                            </label>
                            <textarea 
                                class="form-control <?= isset($errors['bug_description']) ? 'is-invalid' : '' ?>" 
                                id="bug_description" 
                                name="bug_description" 
                                rows="5" 
                                placeholder="Describe the bug you found. For example: 'The search button doesn't work when I try to find restaurants near me' or 'The map is not loading on the restaurant page'"
                                required
                            ><?= old('bug_description') ?></textarea>
                            <?php if (isset($errors['bug_description'])): ?>
                                <div class="invalid-feedback"><?= $errors['bug_description'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Please be as detailed as possible. The more information you provide, the faster we can fix it!
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="page_url" class="form-label">
                                <i class="fas fa-link text-primary me-2"></i>
                                Where did this happen?
                            </label>
                            <input 
                                type="url" 
                                class="form-control <?= isset($errors['page_url']) ? 'is-invalid' : '' ?>" 
                                id="page_url" 
                                name="page_url" 
                                placeholder="https://georgianfoodnearme.com/..."
                                value="<?= old('page_url') ?>"
                            >
                            <?php if (isset($errors['page_url'])): ?>
                                <div class="invalid-feedback"><?= $errors['page_url'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Copy and paste the URL where you noticed the problem (optional)
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="browser_info" class="form-label">
                                <i class="fas fa-browser text-info me-2"></i>
                                Browser & Device Info
                            </label>
                            <input 
                                type="text" 
                                class="form-control <?= isset($errors['browser_info']) ? 'is-invalid' : '' ?>" 
                                id="browser_info" 
                                name="browser_info" 
                                placeholder="Chrome on iPhone, Firefox on Windows, Safari on Mac, etc."
                                value="<?= old('browser_info') ?>"
                            >
                            <?php if (isset($errors['browser_info'])): ?>
                                <div class="invalid-feedback"><?= $errors['browser_info'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                What browser and device are you using? (optional but helpful)
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="user_email" class="form-label">
                                <i class="fas fa-envelope text-success me-2"></i>
                                Your Email (Optional)
                            </label>
                            <input 
                                type="email" 
                                class="form-control <?= isset($errors['user_email']) ? 'is-invalid' : '' ?>" 
                                id="user_email" 
                                name="user_email" 
                                placeholder="your.email@example.com"
                                value="<?= old('user_email') ?>"
                            >
                            <?php if (isset($errors['user_email'])): ?>
                                <div class="invalid-feedback"><?= $errors['user_email'] ?></div>
                            <?php endif; ?>
                            <div class="form-text">
                                <i class="fas fa-user-secret me-1"></i>
                                Leave empty to report anonymously. We'll only use this to follow up if needed.
                            </div>
                        </div>

                        <div class="bg-light p-4 rounded mb-4">
                            <h5 class="text-secondary mb-3">
                                <i class="fas fa-shield-alt me-2"></i>Privacy Notice
                            </h5>
                            <ul class="mb-0 text-sm">
                                <li>Bug reports help us improve the website for everyone</li>
                                <li>Your IP address and browser info are collected for technical purposes</li>
                                <li>Email is optional - you can report bugs completely anonymously</li>
                                <li>We don't share your information with third parties</li>
                                <li>Reports are only used to fix technical issues</li>
                            </ul>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-danger btn-lg px-5" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send Bug Report
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <a href="<?= base_url() ?>" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill current page URL if coming from another page
    const pageUrlField = document.getElementById('page_url');
    if (!pageUrlField.value && document.referrer) {
        pageUrlField.value = document.referrer;
    }
    
    // Auto-detect browser info
    const browserField = document.getElementById('browser_info');
    if (!browserField.value) {
        const browser = getBrowserInfo();
        browserField.value = browser;
    }
    
    // Form submission handling
    const form = document.getElementById('bugReportForm');
    const submitBtn = document.getElementById('submitBtn');
    
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
    });
});

function getBrowserInfo() {
    const ua = navigator.userAgent;
    let browser = 'Unknown';
    let os = 'Unknown';
    
    // Detect browser
    if (ua.includes('Chrome') && !ua.includes('Edg')) browser = 'Chrome';
    else if (ua.includes('Firefox')) browser = 'Firefox';
    else if (ua.includes('Safari') && !ua.includes('Chrome')) browser = 'Safari';
    else if (ua.includes('Edg')) browser = 'Edge';
    else if (ua.includes('Opera')) browser = 'Opera';
    
    // Detect OS
    if (ua.includes('Windows')) os = 'Windows';
    else if (ua.includes('Mac')) os = 'Mac';
    else if (ua.includes('Linux')) os = 'Linux';
    else if (ua.includes('Android')) os = 'Android';
    else if (ua.includes('iPhone') || ua.includes('iPad')) os = 'iOS';
    
    return `${browser} on ${os}`;
}
</script>

<?= $this->endSection() ?>