<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiLogModel extends Model
{
    protected $table = 'api_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'api_provider',
        'endpoint',
        'request_method',
        'request_data',
        'response_data',
        'response_code',
        'response_time',
        'api_cost',
        'user_ip',
        'user_agent',
        'created_at'
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = false;
    
    /**
     * Логирование API запроса
     */
    public function logApiRequest($provider, $endpoint, $requestData, $responseData, $responseCode, $responseTime, $cost = null)
    {
        $request = \Config\Services::request();
        
        $logData = [
            'api_provider' => $provider,
            'endpoint' => $endpoint,
            'request_method' => $request->getMethod(),
            'request_data' => json_encode($requestData),
            'response_data' => is_string($responseData) ? $responseData : json_encode($responseData),
            'response_code' => $responseCode,
            'response_time' => $responseTime,
            'api_cost' => $cost,
            'user_ip' => $request->getIPAddress(),
            'user_agent' => $request->getUserAgent()->getAgentString()
        ];
        
        return $this->insert($logData);
    }
    
    /**
     * Получение статистики по API запросам
     */
    public function getApiStats($provider = null, $days = 30)
    {
        $builder = $this->builder();
        
        if ($provider) {
            $builder->where('api_provider', $provider);
        }
        
        $builder->where('created_at >=', date('Y-m-d H:i:s', strtotime("-{$days} days")));
        
        return $builder->select([
            'api_provider',
            'COUNT(*) as total_requests',
            'AVG(response_time) as avg_response_time',
            'SUM(api_cost) as total_cost',
            'COUNT(CASE WHEN response_code = 200 THEN 1 END) as successful_requests',
            'COUNT(CASE WHEN response_code != 200 THEN 1 END) as failed_requests'
        ])
        ->groupBy('api_provider')
        ->get()
        ->getResultArray();
    }
    
    /**
     * Очистка старых логов
     */
    public function cleanupOldLogs($daysToKeep = 90)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));
        return $this->where('created_at <', $cutoffDate)->delete();
    }
}