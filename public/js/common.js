/**
 * SmartFood - Funciones Comunes
 * Funciones utilitarias reutilizables para toda la aplicación
 */

// URL base de la API
const API_BASE_URL = window.location.origin;

/**
 * Hacer peticiones HTTP para rutas web (sin Bearer token, solo CSRF)
 * @param {string} url - Endpoint de la web
 * @param {Object} options - Opciones de la petición (method, body, headers)
 * @returns {Promise} Respuesta JSON de la API
 */
async function webRequest(url, options = {}) {
    // Obtener token CSRF del HTML
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Headers por defecto para rutas web
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin' // Incluir cookies de sesión
    };

    // Añadir token CSRF para peticiones POST/PUT/DELETE
    if (csrfToken && options.method && options.method.toUpperCase() !== 'GET') {
        defaultOptions.headers['X-CSRF-TOKEN'] = csrfToken;
    }

    // Combinar opciones
    const finalOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };

    try {
        const response = await fetch(`${API_BASE_URL}${url}`, finalOptions);

        if (!response.ok) {
            // Si es 401, el usuario no está autenticado
            if (response.status === 401) {
                window.location.href = '/login';
                return;
            }

            // Si es 419, el token CSRF ha expirado
            if (response.status === 419) {
                window.location.reload();
                return;
            }
        }

        return await response.json();
    } catch (error) {
        console.error('Error en petición web:', error);
        throw error;
    }
}

/**
 * Hacer peticiones HTTP con autenticación automática (para APIs)
 * @param {string} url - Endpoint de la API
 * @param {Object} options - Opciones de la petición (method, body, headers)
 * @returns {Promise} Respuesta JSON de la API
 */
async function apiRequest(url, options = {}) {
    // Obtener token de localStorage
    const token = localStorage.getItem('auth_token');

    // Obtener token CSRF del HTML
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Headers por defecto
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    };

    // Añadir token de autenticación si existe
    if (token) {
        defaultOptions.headers['Authorization'] = `Bearer ${token}`;
    }

    // Añadir token CSRF para peticiones POST/PUT/DELETE
    if (csrfToken && options.method && options.method.toUpperCase() !== 'GET') {
        defaultOptions.headers['X-CSRF-TOKEN'] = csrfToken;
    }

    // Combinar opciones
    const finalOptions = {
        ...defaultOptions,
        ...options,
        headers: {
            ...defaultOptions.headers,
            ...options.headers
        }
    };

    try {
        const response = await fetch(`${API_BASE_URL}${url}`, finalOptions);
        return await response.json();
    } catch (error) {
        console.error('Error en petición API:', error);
        throw error;
    }
}

/**
 * Mostrar mensaje temporal al usuario
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - 'success' o 'error'
 */
function showMessage(message, type = 'success') {
    // Quitar mensaje anterior si existe
    const existingMessage = document.getElementById('feedback-message');
    if (existingMessage) {
        existingMessage.remove();
    }

    // Color según tipo de mensaje usando la paleta simplificada
    const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';

    // Crear elemento del mensaje
    const messageHtml = `
        <div id="feedback-message" class="fixed top-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300">
            ${escapeHtml(message)}
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', messageHtml);

    // Auto-eliminar después de 3 segundos
    setTimeout(() => {
        const msg = document.getElementById('feedback-message');
        if (msg) {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 300);
        }
    }, 3000);
}

/**
 * Validar formato de email
 * @param {string} email - Email a validar
 * @returns {boolean} true si es válido
 */
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

/**
 * Escapar HTML para evitar XSS
 * @param {string} text - Texto a escapar
 * @returns {string} Texto escapado
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Generar avatar con iniciales
 * @param {string} name - Nombre
 * @param {string} surname - Apellido (opcional)
 * @returns {string} Iniciales en mayúsculas
 */
function generateAvatar(name, surname = '') {
    const initials = `${name.charAt(0)}${surname.charAt(0)}`.toUpperCase();
    return initials;
}

/**
 * Cerrar sesión y limpiar datos
 */
async function logout() {
    try {
        // Notificar al servidor
        await apiRequest('/logout', { method: 'POST' });
    } catch (error) {
        console.error('Error during logout:', error);
    } finally {
        // Limpiar datos locales siempre
        localStorage.removeItem('auth_token');
        localStorage.removeItem('smartfood_conversations');
        localStorage.removeItem('smartfood_settings');

        // Limpiar configuraciones de tema usando el nuevo sistema global
        if (window.DarkModeManager) {
            window.DarkModeManager.clearSettings();
        }

        // Limpiar configuraciones si la función existe (sistema anterior)
        if (typeof clearSettingsOnLogout === 'function') {
            clearSettingsOnLogout();
        }

        // Redirigir a login
        window.location.href = '/login';
    }
}
