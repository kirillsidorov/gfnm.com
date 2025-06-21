// Добавить в отдельный файл public/assets/js/seo-helper.js или включить в основной admin.js

class SeoHelper {
    constructor() {
        this.initClipboard();
        this.initKeyboardShortcuts();
        this.initTooltips();
        this.showKeyboardShortcuts();
    }
    
    // Инициализация функций копирования URL
    initClipboard() {
        // Добавляем кнопки копирования к URL превью
        document.querySelectorAll('.url-preview').forEach(preview => {
            this.addCopyButton(preview);
        });
    }
    
    // Добавление кнопки копирования
    addCopyButton(previewElement) {
        const container = previewElement.parentNode;
        if (!container.classList.contains('url-preview-container')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'url-preview-container';
            container.insertBefore(wrapper, previewElement);
            wrapper.appendChild(previewElement);
        }
        
        const copyBtn = document.createElement('button');
        copyBtn.className = 'btn btn-outline-secondary btn-sm copy-url-btn';
        copyBtn.innerHTML = '<i class="fas fa-copy"></i>';
        copyBtn.type = 'button';
        copyBtn.title = 'Copy URL';
        
        copyBtn.addEventListener('click', () => {
            const url = previewElement.textContent;
            this.copyToClipboard(url);
            this.showCopySuccess(copyBtn);
        });
        
        previewElement.parentNode.appendChild(copyBtn);
    }
    
    // Копирование в буфер обмена
    async copyToClipboard(text) {
        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
            } else {
                // Fallback для старых браузеров
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                document.execCommand('copy');
                textArea.remove();
            }
        } catch (err) {
            console.error('Failed to copy URL:', err);
        }
    }
    
    // Показать успешное копирование
    showCopySuccess(button) {
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.add('btn-success');
        button.classList.remove('btn-outline-secondary');
        
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 1500);
    }
    
    // Горячие клавиши
    initKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl+Alt+S - сохранить форму
            if (e.ctrlKey && e.altKey && e.key === 's') {
                e.preventDefault();
                document.querySelector('form').submit();
            }
            
            // Ctrl+Alt+G - генерировать SEO URL
            if (e.ctrlKey && e.altKey && e.key === 'g') {
                e.preventDefault();
                const generateBtn = document.getElementById('generateSeoUrl');
                if (generateBtn) generateBtn.click();
            }
            
            // Ctrl+Alt+C - скопировать основной URL
            if (e.ctrlKey && e.altKey && e.key === 'c') {
                e.preventDefault();
                const mainUrl = document.getElementById('restaurant-url-preview');
                if (mainUrl) {
                    this.copyToClipboard(mainUrl.textContent);
                    this.showNotification('URL copied to clipboard');
                }
            }
            
            // Escape - закрыть модальные окна/уведомления
            if (e.key === 'Escape') {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.remove();
                });
            }
        });
    }
    
    // Инициализация подсказок
    initTooltips() {
        // Bootstrap tooltips
        if (typeof bootstrap !== 'undefined') {
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(tooltip => {
                new bootstrap.Tooltip(tooltip);
            });
        }
        
        // Кастомные подсказки для полей SEO
        this.addSeoTooltips();
    }
    
    // Добавление подсказок для SEO полей
    addSeoTooltips() {
        const slugField = document.getElementById('slug');
        const seoUrlField = document.getElementById('seo_url');
        
        if (slugField) {
            slugField.setAttribute('data-bs-toggle', 'tooltip');
            slugField.setAttribute('data-bs-placement', 'top');
            slugField.setAttribute('title', 'Short URL identifier (3-50 chars, a-z, 0-9, hyphens only)');
        }
        
        if (seoUrlField) {
            seoUrlField.setAttribute('data-bs-toggle', 'tooltip');
            seoUrlField.setAttribute('data-bs-placement', 'top');
            seoUrlField.setAttribute('title', 'Full SEO URL path. Format: restaurant-name-city');
        }
    }
    
    // Показать информацию о горячих клавишах
    showKeyboardShortcuts() {
        const shortcutsDiv = document.createElement('div');
        shortcutsDiv.className = 'keyboard-shortcuts';
        shortcutsDiv.innerHTML = `
            <div><kbd>Ctrl</kbd> + <kbd>Alt</kbd> + <kbd>S</kbd> Save form</div>
            <div><kbd>Ctrl</kbd> + <kbd>Alt</kbd> + <kbd>G</kbd> Generate SEO URL</div>
            <div><kbd>Ctrl</kbd> + <kbd>Alt</kbd> + <kbd>C</kbd> Copy main URL</div>
            <div><kbd>Esc</kbd> Close alerts</div>
        `;
        
        document.body.appendChild(shortcutsDiv);
        
        // Показываем подсказки на 3 секунды при загрузке страницы
        setTimeout(() => {
            shortcutsDiv.classList.add('show');
        }, 1000);
        
        setTimeout(() => {
            shortcutsDiv.classList.remove('show');
        }, 4000);
        
        // Показываем при нажатии F1
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F1') {
                e.preventDefault();
                shortcutsDiv.classList.toggle('show');
            }
        });
    }
    
    // Показать уведомление
    showNotification(message, type = 'info', duration = 3000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Автоматическое удаление
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
    }
    
    // Валидация SEO полей в реальном времени
    static validateSeoField(field, minLength = 3) {
        const value = field.value;
        const errors = [];
        
        if (value.length > 0 && value.length < minLength) {
            errors.push(`Minimum ${minLength} characters required`);
        }
        
        if (!/^[a-z0-9-]*$/.test(value)) {
            errors.push('Only lowercase letters, numbers, and hyphens allowed');
        }
        
        if (value.startsWith('-') || value.endsWith('-')) {
            errors.push('Cannot start or end with hyphen');
        }
        
        if (value.includes('--')) {
            errors.push('Cannot contain consecutive hyphens');
        }
        
        return {
            valid: errors.length === 0,
            errors: errors
        };
    }
    
    // Генерация предложений для SEO URL
    static generateSeoSuggestions(restaurantName, cityName) {
        const restaurantSlug = SeoHelper.createSlug(restaurantName);
        const citySlug = SeoHelper.createSlug(cityName);
        
        return [
            `${restaurantSlug}-restaurant-${citySlug}`,
            `${restaurantSlug}-${citySlug}`,
            `${restaurantSlug}-georgian-restaurant-${citySlug}`,
            `${restaurantSlug}-tbilisi-restaurant-${citySlug}`,
            `best-${restaurantSlug}-${citySlug}`
        ];
    }
    
    // Создание slug из текста
    static createSlug(text) {
        return text
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }
    
    // Проверка доступности URL через API
    static async checkUrlAvailability(url, type = 'seo_url', excludeId = null) {
        try {
            const response = await fetch('/api/check-url-availability', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    url: url,
                    type: type,
                    exclude_id: excludeId
                })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error checking URL availability:', error);
            return { available: false, error: 'Network error' };
        }
    }
}

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
    new SeoHelper();
});

// Экспорт для использования в других скриптах
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SeoHelper;
}