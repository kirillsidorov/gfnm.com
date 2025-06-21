<?php

namespace App\Controllers;

class Pages extends BaseController
{
    public function index()
    {
        $data = [
            'title' => 'About Us - Georgian Food Near Me',
            'meta_description' => 'Learn about Georgian Food Near Me - your trusted guide to finding authentic Georgian restaurants and cuisine in your area.',
            'canonical_url' => base_url('about')
        ];

        return view('pages/about', $data);
    }

    public function privacy()
    {
        $data = [
            'title' => 'Privacy Policy - Georgian Food Near Me',
            'meta_description' => 'Our privacy policy explains how we collect, use, and protect your personal information on Georgian Food Near Me.',
            'canonical_url' => base_url('privacy')
        ];

        return view('pages/privacy', $data);
    }

        public function terms()
    {
        $data = [
            'title' => 'Terms of Service - Georgian Food Near Me',
            'meta_description' => 'Read our terms of service and user agreement for using Georgian Food Near Me directory platform.',
            'canonical_url' => base_url('terms')
        ];

        return view('pages/terms', $data);
    }
}