/**
 * User Utils - Utilidades de usuario
 * Funciones comunes para gestión de usuario y autenticación
 */

/**
 * Cargar datos del usuario desde el servidor
 * Actualiza el avatar y la información del usuario en la interfaz
 */
async function loadUserData() {
    try {
        // Petición al endpoint de usuario
        const response = await fetch('/user/me', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });

        if (response.ok) {
            const data = await response.json();
            const user = data.data || data.user;

            if (user) {
                // Actualizar avatar en la sidebar con las iniciales del usuario
                updateUserAvatar(user.name);

                // Actualizar nombre si hay elemento para ello
                updateUserName(user.name, user.surname);
            }
        }
    } catch (error) {
        console.error('Error cargando datos de usuario:', error);
    }
}

/**
 * Actualizar avatar del usuario con sus iniciales
 * @param {string} name - Nombre del usuario
 */
function updateUserAvatar(name) {
    const userAvatar = document.getElementById('user-avatar');
    if (userAvatar && name) {
        // Usar la primera letra del nombre en mayúscula
        userAvatar.textContent = name.charAt(0).toUpperCase();
    }
}

/**
 * Actualizar nombre completo del usuario en la interfaz
 * @param {string} name - Nombre del usuario
 * @param {string} surname - Apellido del usuario
 */
function updateUserName(name, surname) {
    const userNameElement = document.getElementById('user-name');
    if (userNameElement && name) {
        userNameElement.textContent = surname ? `${name} ${surname}` : name;
    }
}

/**
 * Generar avatar con iniciales
 * @param {string} name - Nombre del usuario
 * @param {string} surname - Apellido del usuario (opcional)
 * @returns {string} Iniciales para el avatar
 */
function generateAvatar(name, surname = '') {
    if (!name) return '?';

    let initials = name.charAt(0).toUpperCase();
    if (surname) {
        initials += surname.charAt(0).toUpperCase();
    }

    return initials;
}

/**
 * Cerrar sesión del usuario
 * Envía petición de logout y redirige al login
 */
async function logout() {
    try {
        // Enviar petición de logout
        const response = await fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        });

        if (response.ok) {
            // Redirigir al login
            window.location.href = '/login';
        } else {
            console.error('Error en logout:', response.statusText);
            // Forzar redirección en caso de error
            window.location.href = '/login';
        }
    } catch (error) {
        console.error('Error durante logout:', error);
        // Forzar redirección en caso de error de red
        window.location.href = '/login';
    }
}

/**
 * Función auxiliar para hacer peticiones API con manejo de errores
 * @param {string} url - URL de la API
 * @param {Object} options - Opciones de fetch
 * @returns {Promise<Object>} Respuesta parseada
 */
async function apiRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    };

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
        const response = await fetch(url, finalOptions);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    } catch (error) {
        console.error('API Request error:', error);
        throw error;
    }
}

/**
 * Escapar HTML para prevenir XSS
 * @param {string} text - Texto a escapar
 * @returns {string} Texto escapado
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Mostrar mensaje temporal en la interfaz
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo de mensaje ('success', 'error', 'info', 'warning')
 */
function showMessage(message, type = 'info') {
    // Crear elemento de mensaje
    const messageElement = document.createElement('div');

    // Clases CSS según el tipo
    const typeClasses = {
        success: 'bg-green-100 border-green-400 text-green-700',
        error: 'bg-red-100 border-red-400 text-red-700',
        warning: 'bg-yellow-100 border-yellow-400 text-yellow-700',
        info: 'bg-blue-100 border-blue-400 text-blue-700'
    };

    messageElement.className = `fixed top-4 right-4 z-50 border px-4 py-3 rounded ${typeClasses[type] || typeClasses.info}`;
    messageElement.textContent = message;

    // Añadir al DOM
    document.body.appendChild(messageElement);

    // Eliminar automáticamente después de 4 segundos
    setTimeout(() => {
        if (messageElement.parentNode) {
            messageElement.parentNode.removeChild(messageElement);
        }
    }, 4000);
}
