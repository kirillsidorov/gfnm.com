<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail = 'noreply@georgianfoodnearme.com';
    public string $fromName = 'Georgian Food Near Me';
    
    // SMTP Configuration based on your hosting settings
    public string $protocol = 'smtp';
    public string $SMTPHost = 'mail.georgianfoodnearme.com';
    public string $SMTPUser = 'noreply@georgianfoodnearme.com';
    public string $SMTPPass = '?GptReK&xw](kD;['; // Set the password you created in cPanel
    public int $SMTPPort = 465; // SSL/TLS port
    public string $SMTPCrypto = 'ssl'; // Use SSL encryption
    
    // Alternative settings if SSL doesn't work:
     //public int $SMTPPort = 587;
     //public string $SMTPCrypto = 'tls';
    
    public string $mailType = 'text'; // Use 'text' for bug reports (more reliable)
    public string $charset = 'UTF-8';
    public bool $validate = true;
    public int $priority = 3;
    public string $CRLF = "\r\n";
    public string $newline = "\r\n";
    public bool $BCCBatchMode = false;
    public int $BCCBatchSize = 200;
    public bool $DSN = false;
    
    // Additional settings for better delivery
    public int $SMTPTimeout = 60;
    public bool $SMTPKeepAlive = false;
    public string $userAgent = 'Georgian Food Near Me';
}

/*
SETUP CHECKLIST:

✅ 1. Email noreply@georgianfoodnearme.com created in cPanel
✅ 2. SMTP settings from your hosting:
     - Server: mail.georgianfoodnearme.com  
     - Port: 465 (SSL) or 587 (TLS)
     - Authentication required

⚠️ 3. UPDATE PASSWORD:
   Replace 'your_password_here' with actual password from cPanel

🧪 4. TEST SETTINGS:
   If port 465 doesn't work, try:
   - SMTPPort = 587
   - SMTPCrypto = 'tls'

📧 5. EMAIL WILL GO TO:
   info@georgianfoodnearme.com (as set in BugReport controller)
*/