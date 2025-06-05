<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class Test extends Controller
{
    public function database()
    {
        echo "<h1>Тест подключения к базе данных</h1>";
        
        try {
            // Получаем экземпляр базы данных
            $db = Database::connect();
            
            // Проверяем подключение
            if ($db->connect()) {
                echo "<p style='color: green;'>✅ Подключение к базе данных успешно!</p>";
                
                // Показываем информацию о БД
                echo "<h3>Информация о подключении:</h3>";
                echo "<ul>";
                echo "<li><strong>Хост:</strong> " . $db->hostname . "</li>";
                echo "<li><strong>База данных:</strong> " . $db->database . "</li>";
                echo "<li><strong>Пользователь:</strong> " . $db->username . "</li>";
                echo "<li><strong>Драйвер:</strong> " . $db->DBDriver . "</li>";
                echo "</ul>";
                
                // Пробуем выполнить простой запрос
                $query = $db->query("SELECT VERSION() as version");
                $result = $query->getRow();
                
                if ($result) {
                    echo "<p><strong>Версия MySQL:</strong> " . $result->version . "</p>";
                }
                
                // Показываем список таблиц
                $tables = $db->listTables();
                echo "<h3>Таблицы в базе данных (" . count($tables) . "):</h3>";
                if (!empty($tables)) {
                    echo "<ul>";
                    foreach ($tables as $table) {
                        echo "<li>" . $table . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p>Таблиц пока нет</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ Не удалось подключиться к базе данных</p>";
            }
            
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Ошибка подключения: " . $e->getMessage() . "</p>";
        }
        
        echo "<br><a href='/'>← Вернуться на главную</a>";
    }

    public function data()
    {
        echo "<h1>Информация из базы данных</h1>";

        try {
            $db = Database::connect();

            $builder = $db->table('restaurants');
            $count = $builder->countAll();

            echo "<p>🍽️ Общее количество ресторанов: <strong>{$count}</strong></p>";
        } catch (\Exception $e) {
            echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
        }

        echo "<br><a href='/'>← Вернуться на главную</a>";
    }
}