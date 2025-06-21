<?php
// =============================================================================
// ОБНОВЛЕННАЯ MODEL - app/Models/RestaurantPhotoModel.php
// =============================================================================

namespace App\Models;

use CodeIgniter\Model;

class RestaurantPhotoModel extends Model
{
    protected $table = 'restaurant_photos';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    // ОБНОВЛЕННЫЕ поля согласно новой структуре таблицы
    protected $allowedFields = [
        'restaurant_id', 'file_path', 'photo_reference', 
        'width', 'height', 'file_size', 'alt_text',
        'is_primary', 'sort_order'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Получить все фото ресторана
     */
    public function getRestaurantPhotos($restaurantId)
    {
        return $this->where('restaurant_id', $restaurantId)
                   ->orderBy('is_primary', 'DESC')
                   ->orderBy('sort_order', 'ASC')
                   ->orderBy('id', 'ASC')
                   ->findAll();
    }

    /**
     * Получить главное фото ресторана
     */
    public function getMainPhoto($restaurantId)
    {
        return $this->where('restaurant_id', $restaurantId)
                   ->where('is_primary', 1)
                   ->first();
    }

    /**
     * Получить фото галереи (не главные)
     */
    public function getGalleryPhotos($restaurantId)
    {
        return $this->where('restaurant_id', $restaurantId)
                   ->where('is_primary', 0)
                   ->orderBy('sort_order', 'ASC')
                   ->orderBy('id', 'ASC')
                   ->findAll();
    }

    /**
     * Установить главное фото
     */
    public function setMainPhoto($restaurantId, $photoId)
    {
        // Сначала убираем флаг is_primary у всех фото ресторана
        $this->where('restaurant_id', $restaurantId)
             ->set(['is_primary' => 0])
             ->update();
        
        // Затем устанавливаем новое главное фото
        return $this->update($photoId, ['is_primary' => 1]);
    }

    /**
     * Добавить фото для ресторана (ОБНОВЛЕННЫЙ метод)
     */
    public function addPhoto($restaurantId, $filePath, $photoReference = null, $options = [])
    {
        $isPrimary = $options['is_primary'] ?? false;
        
        // Если это будет главное фото, сначала убираем флаг у других
        if ($isPrimary) {
            $this->where('restaurant_id', $restaurantId)
                 ->set(['is_primary' => 0])
                 ->update();
        }

        $data = [
            'restaurant_id' => $restaurantId,
            'file_path' => $filePath,
            'photo_reference' => $photoReference,
            'width' => $options['width'] ?? null,
            'height' => $options['height'] ?? null,
            'file_size' => $options['file_size'] ?? null,
            'alt_text' => $options['alt_text'] ?? null,
            'is_primary' => $isPrimary ? 1 : 0,
            'sort_order' => $options['sort_order'] ?? 0
        ];

        return $this->insert($data);
    }

    /**
     * Удалить фото
     */
    public function deletePhoto($photoId)
    {
        return $this->delete($photoId);
    }

    /**
     * Получить количество фото у ресторана
     */
    public function getPhotoCount($restaurantId)
    {
        return $this->where('restaurant_id', $restaurantId)->countAllResults();
    }

    /**
     * Получить рестораны без фотографий
     */
    public function getRestaurantsWithoutPhotos($limit = 20)
    {
        $db = \Config\Database::connect();
        
        return $db->query("
            SELECT r.id, r.name, r.google_place_id, c.name as city_name
            FROM restaurants r
            JOIN cities c ON c.id = r.city_id
            LEFT JOIN restaurant_photos rp ON rp.restaurant_id = r.id
            WHERE r.is_active = 1 
            AND r.google_place_id IS NOT NULL
            AND r.google_place_id != ''
            AND rp.id IS NULL
            ORDER BY r.name
            LIMIT ?
        ", [$limit])->getResultArray();
    }

    /**
     * Проверить есть ли фото с данным photo_reference
     */
    public function photoReferenceExists($photoReference)
    {
        return $this->where('photo_reference', $photoReference)->countAllResults() > 0;
    }
}