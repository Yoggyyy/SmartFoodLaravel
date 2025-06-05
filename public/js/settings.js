/**
 * SmartFood - Configuraciones
 * Manejo de configuraciones de la aplicación con Tailwind nativo
 */

document.addEventListener('DOMContentLoaded', function() {
    // Cargar configuraciones guardadas al iniciar
    loadSettings();

    // Configurar eventos de configuración
    document.getElementById('language-select').addEventListener('change', changeLanguage);
    document.getElementById('email-notifications').addEventListener('change', toggleEmailNotifications);
    document.getElementById('promotional-emails').addEventListener('change', togglePromotionalEmails);
    document.getElementById('shopping-reminders').addEventListener('change', toggleShoppingReminders);

    // Evento para el botón de guardar configuración
    const saveButton = document.getElementById('save-settings-btn');
    if (saveButton) {
        saveButton.addEventListener('click', saveAllSettings);
    }

    // Sincronizar el toggle del modo oscuro con TailwindDarkMode
    syncDarkModeToggle();
});

/**
 * Sincronizar el toggle de modo oscuro con el sistema TailwindDarkMode
 */
function syncDarkModeToggle() {
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    if (darkModeToggle && window.TailwindDarkMode) {
        // Sincronizar estado inicial
        darkModeToggle.checked = window.TailwindDarkMode.isDarkMode();

        // El evento change ya está manejado por TailwindDarkMode
        // Solo necesitamos sincronizar al cargar
    }
}

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
        darkMode: window.TailwindDarkMode ? window.TailwindDarkMode.isDarkMode() : false,
        emailNotifications: document.getElementById('email-notifications').checked,
        promotionalEmails: document.getElementById('promotional-emails').checked,
        shoppingReminders: document.getElementById('shopping-reminders').checked
    };

    localStorage.setItem('smartfood_settings', JSON.stringify(settings));
}

/**
 * Guardar todas las configuraciones y mostrar feedback al usuario
 */
function saveAllSettings() {
    saveSettings();

    // Mostrar feedback visual
    const feedbackElement = document.getElementById('settings-feedback');
    if (feedbackElement) {
        feedbackElement.classList.remove('hidden');

        // Ocultar el mensaje después de 3 segundos
        setTimeout(() => {
            feedbackElement.classList.add('hidden');
        }, 3000);
    }

    showMessage('¡Configuración guardada exitosamente!', 'success');
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
 * Alternar notificaciones por email
 */
function toggleEmailNotifications() {
    const isEnabled = document.getElementById('email-notifications').checked;

    saveSettings();
    showMessage(`Notificaciones por email ${isEnabled ? 'activadas' : 'desactivadas'}`, 'success');
}

/**
 * Alternar emails promocionales
 */
function togglePromotionalEmails() {
    const isEnabled = document.getElementById('promotional-emails').checked;

    saveSettings();
    showMessage(`Emails promocionales ${isEnabled ? 'activados' : 'desactivados'}`, 'success');
}

/**
 * Alternar recordatorios de compra
 */
function toggleShoppingReminders() {
    const isEnabled = document.getElementById('shopping-reminders').checked;

    saveSettings();
    showMessage(`Recordatorios de compra ${isEnabled ? 'activados' : 'desactivados'}`, 'success');
}

/**
 * Limpiar configuraciones al cerrar sesión
 */
function clearSettingsOnLogout() {
    localStorage.removeItem('smartfood_settings');

    // El modo oscuro Tailwind gestiona su propio almacenamiento
    if (window.TailwindDarkMode) {
        window.TailwindDarkMode.clearSettings();
    }
}

// Exportar función para usar en logout global
window.clearSettingsOnLogout = clearSettingsOnLogout;
