/**
 * SmartFood - Configuraciones
 * Manejo de configuraciones de la aplicación, tema oscuro y notificaciones
 */

document.addEventListener('DOMContentLoaded', function() {
    // Cargar configuraciones guardadas al iniciar
    loadSettings();

    // Configurar eventos de configuración
    document.getElementById('language-select').addEventListener('change', changeLanguage);
    document.getElementById('dark-mode-toggle').addEventListener('change', toggleDarkMode);
    document.getElementById('email-notifications').addEventListener('change', toggleEmailNotifications);
    document.getElementById('promotional-emails').addEventListener('change', togglePromotionalEmails);
    document.getElementById('shopping-reminders').addEventListener('change', toggleShoppingReminders);

    // Configurar acordeón de FAQ
    document.querySelectorAll('.faq-question').forEach(question => {
        question.addEventListener('click', function() {
            const answer = this.nextElementSibling;
            const icon = this.querySelector('.faq-icon');

            // Alternar visibilidad de respuesta
            answer.classList.toggle('hidden');
            icon.style.transform = answer.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        });
    });
});

/**
 * Cargar configuraciones guardadas del localStorage
 */
function loadSettings() {
    const savedSettings = localStorage.getItem('smartfood_settings');

    if (savedSettings) {
        try {
            const settings = JSON.parse(savedSettings);

            // Aplicar configuración de idioma
            if (settings.language) {
                document.getElementById('language-select').value = settings.language;
            }

            // Aplicar modo oscuro
            if (settings.darkMode) {
                document.getElementById('dark-mode-toggle').checked = settings.darkMode;
                applyDarkMode(settings.darkMode);
            }

            // Aplicar configuraciones de notificaciones
            if (settings.emailNotifications !== undefined) {
                document.getElementById('email-notifications').checked = settings.emailNotifications;
            }

            if (settings.promotionalEmails !== undefined) {
                document.getElementById('promotional-emails').checked = settings.promotionalEmails;
            }

            if (settings.shoppingReminders !== undefined) {
                document.getElementById('shopping-reminders').checked = settings.shoppingReminders;
            }

        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }
}

/**
 * Guardar configuraciones actuales en localStorage
 */
function saveSettings() {
    const settings = {
        language: document.getElementById('language-select').value,
        darkMode: document.getElementById('dark-mode-toggle').checked,
        emailNotifications: document.getElementById('email-notifications').checked,
        promotionalEmails: document.getElementById('promotional-emails').checked,
        shoppingReminders: document.getElementById('shopping-reminders').checked
    };

    localStorage.setItem('smartfood_settings', JSON.stringify(settings));
}

/**
 * Cambiar idioma de la aplicación
 */
function changeLanguage() {
    const selectedLanguage = document.getElementById('language-select').value;

    saveSettings();
    showMessage(`Idioma cambiado a ${selectedLanguage === 'es' ? 'Español' : 'English'}`, 'success');

    // TODO: Implementar la lógica de internacionalización
    // Por ahora solo mostramos el mensaje de confirmación
}

/**
 * Alternar entre modo claro y oscuro
 */
function toggleDarkMode() {
    const isDarkMode = document.getElementById('dark-mode-toggle').checked;

    applyDarkMode(isDarkMode);
    saveSettings();

    showMessage(`Modo ${isDarkMode ? 'oscuro' : 'claro'} activado`, 'success');
}

/**
 * Aplicar estilos de modo oscuro
 * @param {boolean} isDarkMode - true para activar modo oscuro
 */
function applyDarkMode(isDarkMode) {
    const body = document.body;

    if (isDarkMode) {
        body.classList.add('dark-mode');
        updateDarkModeStyles();
    } else {
        body.classList.remove('dark-mode');
        removeDarkModeStyles();
    }
}

/**
 * Crear y aplicar estilos CSS para modo oscuro
 */
function updateDarkModeStyles() {
    // Crear elemento de estilos si no existe
    let darkModeStyles = document.getElementById('dark-mode-styles');

    if (!darkModeStyles) {
        darkModeStyles = document.createElement('style');
        darkModeStyles.id = 'dark-mode-styles';
        document.head.appendChild(darkModeStyles);
    }

    // Definir estilos CSS para modo oscuro
    darkModeStyles.textContent = `
        .dark-mode {
            background-color: #1a202c !important;
            color: #e2e8f0 !important;
        }

        .dark-mode .bg-white {
            background-color: #2d3748 !important;
            color: #e2e8f0 !important;
        }

        .dark-mode .bg-gray-50 {
            background-color: #2d3748 !important;
        }

        .dark-mode .bg-gray-100 {
            background-color: #4a5568 !important;
        }

        .dark-mode .text-gray-600 {
            color: #cbd5e0 !important;
        }

        .dark-mode .text-gray-700 {
            color: #e2e8f0 !important;
        }

        .dark-mode .text-gray-800 {
            color: #f7fafc !important;
        }

        .dark-mode .text-gray-900 {
            color: #ffffff !important;
        }

        .dark-mode .border-gray-200 {
            border-color: #4a5568 !important;
        }

        .dark-mode .border-gray-300 {
            border-color: #718096 !important;
        }

        .dark-mode input,
        .dark-mode select,
        .dark-mode textarea {
            background-color: #4a5568 !important;
            color: #e2e8f0 !important;
            border-color: #718096 !important;
        }

        .dark-mode .shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.3), 0 1px 2px 0 rgba(0, 0, 0, 0.2) !important;
        }

        .dark-mode .hover\\:bg-green-50:hover {
            background-color: #2f855a !important;
        }

        .dark-mode .hover\\:bg-gray-50:hover {
            background-color: #4a5568 !important;
        }
    `;
}

/**
 * Remover estilos de modo oscuro del DOM
 */
function removeDarkModeStyles() {
    const darkModeStyles = document.getElementById('dark-mode-styles');
    if (darkModeStyles) {
        darkModeStyles.remove();
    }
}

/**
 * Alternar notificaciones por email
 */
function toggleEmailNotifications() {
    const isEnabled = document.getElementById('email-notifications').checked;

    saveSettings();
    showMessage(`Notificaciones por email ${isEnabled ? 'activadas' : 'desactivadas'}`, 'success');

    // TODO: Enviar configuración al servidor para actualizar preferencias
}

/**
 * Alternar emails promocionales
 */
function togglePromotionalEmails() {
    const isEnabled = document.getElementById('promotional-emails').checked;

    saveSettings();
    showMessage(`Emails promocionales ${isEnabled ? 'activados' : 'desactivados'}`, 'success');

    // TODO: Enviar configuración al servidor
}

/**
 * Alternar recordatorios de compras
 */
function toggleShoppingReminders() {
    const isEnabled = document.getElementById('shopping-reminders').checked;

    saveSettings();
    showMessage(`Recordatorios de compras ${isEnabled ? 'activados' : 'desactivados'}`, 'success');

    // TODO: Enviar configuración al servidor
}

/**
 * Limpiar configuraciones al cerrar sesión
 * Esta función es llamada desde common.js durante el logout
 */
function clearSettingsOnLogout() {
    // Remover modo oscuro si está activo
    removeDarkModeStyles();
    document.body.classList.remove('dark-mode');

    // No eliminamos las configuraciones del localStorage
    // para que se mantengan cuando el usuario vuelva a iniciar sesión
    console.log('Configuraciones de sesión limpiadas');
}

// Exportar función para usar en logout global
window.clearSettingsOnLogout = clearSettingsOnLogout;
