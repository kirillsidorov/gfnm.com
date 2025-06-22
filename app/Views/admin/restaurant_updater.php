<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Обновление ресторанов - Georgian Food Near Me</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-custom {
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .progress-card {
            display: none;
            border-left: 4px solid #28a745;
        }
        .log-container {
            max-height: 400px;
            overflow-y: auto;
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
        }
        .log-item {
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 5px;
            border-left: 4px solid #17a2b8;
            background: white;
        }
        .log-success {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .log-error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .animate-pulse {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-sync-alt text-primary"></i>
                        Обновление ресторанов
                    </h1>
                    <a href="/admin" class="btn btn-outline-secondary btn-custom">
                        <i class="fas fa-arrow-left"></i> Назад в админку
                    </a>
                </div>
            </div>
        </div>

        <!-- Статистика -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card h-100">
                    <div class="card-body text-center">
                        <i class="fas fa-utensils fa-2x mb-3"></i>
                        <h4 class="card-title" id="totalRestaurants">-</h4>
                        <p class="card-text">Всего ресторанов</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
                    <div class="card-body text-center">
                        <i class="fas fa-map-marker-alt fa-2x mb-3"></i>
                        <h4 class="card-title" id="withPlaceId">-</h4>
                        <p class="card-text">С Place ID</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100" style="background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%); color: white;">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                        <h4 class="card-title" id="withoutPlaceId">-</h4>
                        <p class="card-text">Без Place ID</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card h-100" style="background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); color: #333;">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x mb-3"></i>
                        <h4 class="card-title" id="needUpdate">-</h4>
                        <p class="card-text">Требуют обновления</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Управление обновлениями -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs"></i>
                            Управление обновлениями
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <button class="btn btn-success btn-custom" onclick="updateAllRestaurants()">
                                <i class="fas fa-sync-alt"></i>
                                Обновить все рестораны с Place ID
                            </button>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <input type="number" class="form-control" id="singleRestaurantId" 
                                           placeholder="ID ресторана" min="1">
                                </div>
                                <div class="col-md-4">
                                    <button class="btn btn-info btn-custom w-100" onclick="updateSingleRestaurant()">
                                        <i class="fas fa-edit"></i>
                                        Обновить
                                    </button>
                                </div>
                            </div>
                            
                            <button class="btn btn-outline-primary btn-custom" onclick="refreshStats()">
                                <i class="fas fa-chart-bar"></i>
                                Обновить статистику
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <!-- Прогресс -->
                <div class="card progress-card mb-4" id="progressCard">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-spinner fa-spin"></i>
                            Прогресс обновления
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" id="progressBar" style="width: 0%">
                                0%
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Обработано: <strong id="processedCount">0</strong></span>
                            <span>Всего: <strong id="totalCount">0</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Логи -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt"></i>
                            Лог операций
                        </h5>
                        <button class="btn btn-sm btn-outline-light" onclick="clearLogs()">
                            <i class="fas fa-trash"></i>
                            Очистить
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="log-container" id="logContainer">
                            <div class="text-muted text-center py-4">
                                <i class="fas fa-info-circle"></i>
                                Логи операций будут отображаться здесь
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Переменные для отслеживания состояния
        let isUpdating = false;
        let updateProgress = 0;

        // Загрузка статистики при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            refreshStats();
        });

        // Обновление статистики
        async function refreshStats() {
            try {
                const response = await fetch('/admin/restaurants/stats');
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('totalRestaurants').textContent = result.data.total_restaurants;
                    document.getElementById('withPlaceId').textContent = result.data.with_place_id;
                    document.getElementById('withoutPlaceId').textContent = result.data.without_place_id;
                    document.getElementById('needUpdate').textContent = result.data.need_update;
                } else {
                    addLog('error', 'Ошибка загрузки статистики: ' + result.message);
                }
            } catch (error) {
                addLog('error', 'Ошибка соединения при загрузке статистики: ' + error.message);
            }
        }

        // Обновление всех ресторанов
        async function updateAllRestaurants() {
            if (isUpdating) {
                alert('Обновление уже выполняется');
                return;
            }

            if (!confirm('Вы уверены, что хотите обновить все рестораны с Place ID? Это может занять много времени.')) {
                return;
            }

            isUpdating = true;
            showProgress();
            addLog('info', 'Начинаем обновление всех ресторанов...');

            try {
                const response = await fetch('/admin/restaurants/update-all', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                
                if (result.success) {
                    addLog('success', result.message);
                    
                    // Отображаем детали
                    if (result.data && result.data.details) {
                        result.data.details.forEach(detail => {
                            const logType = detail.success ? 'success' : 'error';
                            addLog(logType, `${detail.name} (ID: ${detail.id}): ${detail.message}`);
                        });
                    }
                    
                    // Обновляем статистику
                    await refreshStats();
                } else {
                    addLog('error', 'Ошибка обновления: ' + result.message);
                }
            } catch (error) {
                addLog('error', 'Ошибка соединения: ' + error.message);
            } finally {
                isUpdating = false;
                hideProgress();
            }
        }

        // Обновление одного ресторана
        async function updateSingleRestaurant() {
            const restaurantId = document.getElementById('singleRestaurantId').value;
            
            if (!restaurantId) {
                alert('Введите ID ресторана');
                return;
            }

            if (isUpdating) {
                alert('Обновление уже выполняется');
                return;
            }

            isUpdating = true;
            addLog('info', `Обновляем ресторан ID: ${restaurantId}...`);

            try {
                const response = await fetch('/admin/restaurants/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `restaurant_id=${restaurantId}`
                });

                const result = await response.json();
                
                if (result.success) {
                    addLog('success', `Ресторан ID ${restaurantId}: ${result.message}`);
                    if (result.updated_fields) {
                        addLog('info', `Обновленные поля: ${result.updated_fields.join(', ')}`);
                    }
                    await refreshStats();
                } else {
                    addLog('error', `Ресторан ID ${restaurantId}: ${result.message}`);
                }
            } catch (error) {
                addLog('error', 'Ошибка соединения: ' + error.message);
            } finally {
                isUpdating = false;
            }
        }

        // Отображение прогресса
        function showProgress() {
            document.getElementById('progressCard').style.display = 'block';
            updateProgressBar(0, 0, 1);
        }

        // Скрытие прогресса
        function hideProgress() {
            document.getElementById('progressCard').style.display = 'none';
        }

        // Обновление прогресс-бара
        function updateProgressBar(processed, total, current = processed) {
            const percentage = total > 0 ? Math.round((current / total) * 100) : 0;
            
            document.getElementById('progressBar').style.width = percentage + '%';
            document.getElementById('progressBar').textContent = percentage + '%';
            document.getElementById('processedCount').textContent = processed;
            document.getElementById('totalCount').textContent = total;
        }

        // Добавление сообщения в лог
        function addLog(type, message) {
            const logContainer = document.getElementById('logContainer');
            const timestamp = new Date().toLocaleTimeString();
            
            // Удаляем placeholder если есть
            const placeholder = logContainer.querySelector('.text-muted.text-center');
            if (placeholder) {
                placeholder.remove();
            }
            
            const logItem = document.createElement('div');
            logItem.className = `log-item log-${type}`;
            
            let icon = '';
            switch(type) {
                case 'success':
                    icon = '<i class="fas fa-check-circle text-success"></i>';
                    break;
                case 'error':
                    icon = '<i class="fas fa-exclamation-circle text-danger"></i>';
                    break;
                case 'info':
                    icon = '<i class="fas fa-info-circle text-info"></i>';
                    break;
                default:
                    icon = '<i class="fas fa-circle"></i>';
            }
            
            logItem.innerHTML = `
                <div class="d-flex align-items-start">
                    <div class="me-2">${icon}</div>
                    <div class="flex-grow-1">
                        <small class="text-muted">${timestamp}</small><br>
                        ${message}
                    </div>
                </div>
            `;
            
            logContainer.appendChild(logItem);
            logContainer.scrollTop = logContainer.scrollHeight;
            
            // Ограничиваем количество логов
            const logs = logContainer.querySelectorAll('.log-item');
            if (logs.length > 100) {
                logs[0].remove();
            }
        }

        // Очистка логов
        function clearLogs() {
            const logContainer = document.getElementById('logContainer');
            logContainer.innerHTML = `
                <div class="text-muted text-center py-4">
                    <i class="fas fa-info-circle"></i>
                    Логи операций будут отображаться здесь
                </div>
            `;
        }

        // Обработка Enter в поле ID ресторана
        document.getElementById('singleRestaurantId').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                updateSingleRestaurant();
            }
        });

        // Автообновление статистики каждые 30 секунд
        setInterval(refreshStats, 30000);
    </script>
</body>
</html>