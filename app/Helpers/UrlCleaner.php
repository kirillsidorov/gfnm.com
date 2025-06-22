<?php

namespace App\Helpers;

/**
 * Класс для очистки URL от UTM меток и других трекинговых параметров
 */
class UrlCleaner
{
    /**
     * Список параметров для удаления из URL
     */
    private static $trackingParams = [
        // UTM параметры
        'utm_source',
        'utm_medium', 
        'utm_campaign',
        'utm_content',
        'utm_term',
        'utm_id',
        'utm_source_platform',
        'utm_creative_format',
        'utm_marketing_tactic',
        
        // Facebook параметры
        'fbclid',
        'fb_action_ids',
        'fb_action_types',
        'fb_ref',
        'fb_source',
        
        // Google параметры
        'gclid',
        'gclsrc',
        'dclid',
        '_ga',
        '_gl',
        
        // Яндекс параметры
        'yclid',
        'frommarket',
        
        // Другие трекинговые параметры
        'mc_cid',
        'mc_eid',
        'ref',
        'referrer',
        'source',
        'campaign',
        'medium',
        'content',
        '_hsenc',
        '_hsmi',
        'hsCtaTracking',
        'mkt_tok',
        'trk',
        'trkCampaign',
        'sc_camp',
        'sc_channel',
        'sc_content',
        'sc_country',
        'sc_geo',
        'sc_medium',
        'sc_outcome',
        'wbraid',
        'gbraid',
        'affiliate_id',
        'partner_id',
        'clickid',
        'zanpid',
        'at_medium',
        'at_campaign',
        'igshid'
    ];

    /**
     * Очистка URL от трекинговых параметров
     * 
     * @param string $url Исходный URL
     * @return string Очищенный URL
     */
    public static function clean($url)
    {
        if (empty($url)) {
            return '';
        }

        // Парсим URL
        $parsedUrl = parse_url(trim($url));
        
        if ($parsedUrl === false || empty($parsedUrl['host'])) {
            return $url; // Возвращаем исходный URL если не удалось распарсить
        }

        // Собираем базовую часть URL
        $cleanUrl = '';
        
        // Схема (http/https)
        if (isset($parsedUrl['scheme'])) {
            $cleanUrl .= $parsedUrl['scheme'] . '://';
        } else {
            $cleanUrl .= 'https://'; // По умолчанию https
        }

        // Хост
        $cleanUrl .= $parsedUrl['host'];

        // Порт (если не стандартный)
        if (isset($parsedUrl['port']) && 
            !(($parsedUrl['scheme'] === 'http' && $parsedUrl['port'] === 80) ||
              ($parsedUrl['scheme'] === 'https' && $parsedUrl['port'] === 443))) {
            $cleanUrl .= ':' . $parsedUrl['port'];
        }

        // Путь
        if (isset($parsedUrl['path']) && $parsedUrl['path'] !== '/') {
            $cleanUrl .= $parsedUrl['path'];
        }

        // Обрабатываем query параметры
        if (isset($parsedUrl['query'])) {
            $cleanQuery = self::cleanQueryString($parsedUrl['query']);
            if (!empty($cleanQuery)) {
                $cleanUrl .= '?' . $cleanQuery;
            }
        }

        // Фрагмент (якорь)
        if (isset($parsedUrl['fragment'])) {
            $cleanUrl .= '#' . $parsedUrl['fragment'];
        }

        return $cleanUrl;
    }

    /**
     * Очистка query string от трекинговых параметров
     * 
     * @param string $queryString Query string для очистки
     * @return string Очищенная query string
     */
    private static function cleanQueryString($queryString)
    {
        if (empty($queryString)) {
            return '';
        }

        parse_str($queryString, $queryParams);
        
        // Удаляем трекинговые параметры
        foreach (self::$trackingParams as $param) {
            unset($queryParams[$param]);
        }

        // Удаляем параметры, начинающиеся с _
        foreach ($queryParams as $key => $value) {
            if (strpos($key, '_') === 0) {
                unset($queryParams[$key]);
            }
        }

        return http_build_query($queryParams);
    }

    /**
     * Проверка, является ли параметр трекинговым
     * 
     * @param string $param Название параметра
     * @return bool
     */
    public static function isTrackingParam($param)
    {
        return in_array(strtolower($param), self::$trackingParams) || 
               strpos($param, '_') === 0;
    }

    /**
     * Получение списка всех трекинговых параметров
     * 
     * @return array
     */
    public static function getTrackingParams()
    {
        return self::$trackingParams;
    }

    /**
     * Добавление пользовательских трекинговых параметров
     * 
     * @param array $params Массив параметров для добавления
     */
    public static function addTrackingParams(array $params)
    {
        self::$trackingParams = array_unique(array_merge(self::$trackingParams, $params));
    }

    /**
     * Валидация URL
     * 
     * @param string $url URL для проверки
     * @return bool
     */
    public static function isValidUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Нормализация URL (приведение к единому виду)
     * 
     * @param string $url Исходный URL
     * @return string Нормализованный URL
     */
    public static function normalize($url)
    {
        $url = self::clean($url);
        
        // Удаляем trailing slash если это не корень
        if (strlen($url) > 1 && substr($url, -1) === '/') {
            $url = rtrim($url, '/');
        }
        
        // Приводим к нижнему регистру (кроме query параметров)
        $parts = parse_url($url);
        if ($parts) {
            $normalized = strtolower($parts['scheme'] . '://' . $parts['host']);
            
            if (isset($parts['path'])) {
                $normalized .= $parts['path'];
            }
            
            if (isset($parts['query'])) {
                $normalized .= '?' . $parts['query'];
            }
            
            if (isset($parts['fragment'])) {
                $normalized .= '#' . $parts['fragment'];
            }
            
            return $normalized;
        }
        
        return $url;
    }

    /**
     * Извлечение домена из URL
     * 
     * @param string $url Исходный URL
     * @return string Только домен
     */
    public static function getDomain($url)
    {
        $parsedUrl = parse_url($url);
        
        if ($parsedUrl && isset($parsedUrl['host'])) {
            $domain = $parsedUrl['host'];
            
            // Удаляем www если есть
            if (strpos($domain, 'www.') === 0) {
                $domain = substr($domain, 4);
            }
            
            return $domain;
        }
        
        return '';
    }
}