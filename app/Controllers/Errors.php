<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

class Errors extends Controller
{
    public function show404()
    {
        // Устанавливаем HTTP статус 404
        $this->response->setStatusCode(404);
        
        // Возвращаем кастомную 404 страницу
        return view('errors/html/error_404');
    }
}