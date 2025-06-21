<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class AdminAuth extends BaseConfig
{
    /**
     * Ключ доступа к админке
     */
    public $adminKey;
    
    /**
     * Время жизни сессии админа (в секундах)
     * 3600 = 1 час
     */
    public $sessionTimeout = 3600;
    
    /**
     * Редирект после входа
     */
    public $loginRedirect = '/admin/dashboard';
    
    /**
     * Редирект после выхода
     */
    public $logoutRedirect = '/admin/login';
    
    /**
     * Использовать "Запомнить меня"
     */
    public $useRememberMe = true;
    
    /**
     * Время жизни "Запомнить меня" (в днях)
     */
    public $rememberMeExpire = 30;
    
    /**
     * Название cookie для "Запомнить меня"
     */
    public $rememberMeCookie = 'admin_remember_token';

    public function __construct()
    {
        parent::__construct();
        
        // Получаем админский ключ из переменных окружения
        $this->adminKey = env('ADMIN_ACCESS_KEY');
        
        // Проверяем, что ключ установлен
        if (empty($this->adminKey)) {
            log_message('error', 'ADMIN_ACCESS_KEY not set in .env file');
            
            if (ENVIRONMENT === 'development') {
                throw new \RuntimeException('ADMIN_ACCESS_KEY must be set in .env file');
            }
            
            $this->adminKey = 'fallback-key-change-immediately';
        }
    }
    /**
     * Проверка правильности админского ключа
     */
    public function isValidAdminKey(string $key): bool
    {
        return !empty($key) && hash_equals($this->adminKey, $key);
    }
}