<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .brand-logo {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        .login-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 50px;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-card p-5">
                    <!-- Header -->
                    <div class="text-center mb-4">
                        <div class="brand-logo">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <h2 class="fw-bold text-dark mb-2">Админка</h2>
                        <p class="text-muted">Georgian Food Near Me</p>
                    </div>

                    <!-- Alerts -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?= session()->getFlashdata('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle"></i> <?= session()->getFlashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Login Form -->
                    <form method="POST" action="<?= base_url('admin/login') ?>">
                        <div class="mb-4">
                            <label for="admin_key" class="form-label fw-semibold">
                                <i class="fas fa-key me-2"></i>Ключ доступа
                            </label>
                            <input type="password" class="form-control" id="admin_key" name="admin_key" 
                                   placeholder="Введите ключ доступа" required>
                        </div>

                        <?php if ($config->useRememberMe): ?>
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                    <label class="form-check-label" for="remember_me">
                                        Запомнить меня на <?= $config->rememberMeExpire ?> дней
                                    </label>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary login-btn text-white">
                                <i class="fas fa-sign-in-alt me-2"></i>Войти в админку
                            </button>
                        </div>
                    </form>

                    <!-- Footer -->
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>
                            Защищенная зона администратора
                        </small>
                        <br>
                        <a href="<?= base_url() ?>" class="text-decoration-none small">
                            <i class="fas fa-arrow-left me-1"></i>Вернуться на сайт
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Автофокус на поле ввода
        document.getElementById('admin_key').focus();
        
        // Показать/скрыть пароль
        document.addEventListener('DOMContentLoaded', function() {
            const keyInput = document.getElementById('admin_key');
            const toggleButton = document.createElement('button');
            toggleButton.type = 'button';
            toggleButton.className = 'btn btn-outline-secondary position-absolute top-50 end-0 translate-middle-y me-3';
            toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
            toggleButton.style.border = 'none';
            toggleButton.style.background = 'none';
            
            keyInput.parentNode.style.position = 'relative';
            keyInput.parentNode.appendChild(toggleButton);
            
            toggleButton.addEventListener('click', function() {
                if (keyInput.type === 'password') {
                    keyInput.type = 'text';
                    toggleButton.innerHTML = '<i class="fas fa-eye-slash"></i>';
                } else {
                    keyInput.type = 'password';
                    toggleButton.innerHTML = '<i class="fas fa-eye"></i>';
                }
            });
        });
    </script>
</body>
</html>