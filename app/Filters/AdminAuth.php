<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $config = config('AdminAuth');
        $session = \Config\Services::session();
        
        // Проверяем сессию (основной способ авторизации)
        if ($session->get('admin_logged_in')) {
            $loginTime = $session->get('admin_login_time');
            
            // Проверяем таймаут сессии
            if (time() - $loginTime > $config->sessionTimeout) {
                $session->destroy();
                return redirect()->to('/admin/login')->with('error', 'Сессия истекла');
            }
            
            // Обновляем время последней активности
            $session->set('admin_login_time', time());
            return; // Авторизован - продолжаем
        }
        
        // Проверяем cookie "Запомнить меня"
        if ($config->useRememberMe && isset($_COOKIE[$config->rememberMeCookie])) {
            $cookieToken = $_COOKIE[$config->rememberMeCookie];
            $sessionToken = $session->get('admin_remember_token');
            
            if ($cookieToken && $cookieToken === $sessionToken) {
                // Восстанавливаем сессию из cookie
                $session->set([
                    'admin_logged_in' => true,
                    'admin_login_time' => time(),
                    'admin_key' => $config->adminKey
                ]);
                return; // Авторизован через cookie - продолжаем
            } else {
                // Cookie недействителен - удаляем его
                setcookie($config->rememberMeCookie, '', time() - 3600, '/');
            }
        }
        
        // Если не авторизован, редиректим на страницу входа
        return redirect()->to('/admin/login')->with('error', 'Необходима авторизация');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // После выполнения запроса ничего не делаем
    }
}