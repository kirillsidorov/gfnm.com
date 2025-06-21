<?php

namespace App\Controllers;

use CodeIgniter\Email\Email;

class BugReport extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'Report a Bug - Georgian Food Near Me',
            'meta_description' => 'Found a bug on our website? Let us know and help us improve Georgian Food Near Me.',
            'canonical_url' => base_url('bug-report')
        ];

        return view('pages/bug_report', $data);
    }

    public function submit()
    {
        // Validation rules
        $validation = $this->validate([
            'bug_description' => [
                'rules' => 'required|min_length[10]|max_length[2000]',
                'errors' => [
                    'required' => 'Please describe the bug you found.',
                    'min_length' => 'Description must be at least 10 characters.',
                    'max_length' => 'Description cannot exceed 2000 characters.'
                ]
            ],
            'page_url' => [
                'rules' => 'permit_empty|valid_url_strict',
                'errors' => [
                    'valid_url_strict' => 'Please enter a valid URL.'
                ]
            ],
            'user_email' => [
                'rules' => 'permit_empty|valid_email',
                'errors' => [
                    'valid_email' => 'Please enter a valid email address.'
                ]
            ],
            'browser_info' => [
                'rules' => 'permit_empty|max_length[500]',
                'errors' => [
                    'max_length' => 'Browser info cannot exceed 500 characters.'
                ]
            ]
        ]);

        if (!$validation) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Get form data
        $bugDescription = $this->request->getPost('bug_description');
        $pageUrl = $this->request->getPost('page_url') ?: 'Not specified';
        $userEmail = $this->request->getPost('user_email') ?: 'Anonymous';
        $browserInfo = $this->request->getPost('browser_info') ?: 'Not provided';
        $userAgent = $this->request->getUserAgent()->getAgentString();
        $userIp = $this->request->getIPAddress();
        $timestamp = date('Y-m-d H:i:s');

        // Prepare email content
        $emailContent = "
        === BUG REPORT - Georgian Food Near Me ===

        Date/Time: {$timestamp}
        Reporter: {$userEmail}
        IP Address: {$userIp}

        --- Bug Description ---
        {$bugDescription}

        --- Page URL ---
        {$pageUrl}

        --- Browser Information ---
        User Provided: {$browserInfo}
        User Agent: {$userAgent}

        --- Additional Info ---
        This bug report was submitted through the website bug report form.
                ";

        try {
            // Send email
            $email = \Config\Services::email();
            
            $email->setFrom('noreply@georgianfoodnearme.com', 'Georgian Food Near Me - Bug Report');
            $email->setTo('info@georgianfoodnearme.com');
            $email->setSubject('🐛 Bug Report - ' . date('Y-m-d H:i'));
            $email->setMessage($emailContent);

            if ($email->send()) {
                return redirect()->to('/bug-report')->with('success', 'Thank you! Your bug report has been sent successfully. We\'ll look into it soon.');
            } else {
                log_message('error', 'Bug report email failed to send: ' . $email->printDebugger());
                return redirect()->back()->withInput()->with('error', 'Sorry, there was an error sending your bug report. Please try again later.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Bug report submission error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Sorry, there was an error sending your bug report. Please try again later.');
        }
    }
}