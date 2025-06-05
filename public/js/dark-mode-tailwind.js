/**
 * SmartFood - Modo Oscuro con Tailwind Nativo
 * Sistema simplificado usando las utilities dark: de Tailwind
 */

// Configurar Tailwind CSS para modo oscuro
if (typeof tailwind !== 'undefined' && tailwind.config) {
    tailwind.config = {
        darkMode: 'class'
    };
}

const TailwindDarkMode = {
    STORAGE_KEY: 'smartfood_theme',

    /**
     * Inicializar el modo oscuro
     */
    init() {
        this.loadTheme();
        this.setupToggleListeners();
        this.syncWithSystemPreference();
    },

    /**
     * Cargar tema desde localStorage o preferencias del sistema
     */
    loadTheme() {
        const savedTheme = localStorage.getItem(this.STORAGE_KEY);
        const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
            this.enableDark();
        } else {
            this.disableDark();
        }
    },

    /**
     * Habilitar modo oscuro
     */
    enableDark() {
        document.documentElement.classList.add('dark');
        this.updateToggles(true);
    },

    /**
     * Deshabilitar modo oscuro
     */
    disableDark() {
        document.documentElement.classList.remove('dark');
        this.updateToggles(false);
    },

    /**
     * Alternar modo oscuro
     */
    toggle() {
        const isDark = document.documentElement.classList.contains('dark');

        if (isDark) {
            this.disableDark();
            localStorage.setItem(this.STORAGE_KEY, 'light');
            this.showMessage('Modo claro activado');
        } else {
            this.enableDark();
            localStorage.setItem(this.STORAGE_KEY, 'dark');
            this.showMessage('Modo oscuro activado');
        }

        return !isDark;
    },

    /**
     * Configurar listeners para toggles
     */
    setupToggleListeners() {
        const toggles = document.querySelectorAll('#dark-mode-toggle, [data-dark-toggle]');

        toggles.forEach(toggle => {
            toggle.addEventListener('change', (e) => {
                e.preventDefault();
                if (toggle.checked) {
                    this.enableDark();
                    localStorage.setItem(this.STORAGE_KEY, 'dark');
                } else {
                    this.disableDark();
                    localStorage.setItem(this.STORAGE_KEY, 'light');
                }
            });
        });
    },

    /**
     * Actualizar estado de los toggles
     */
    updateToggles(isDark) {
        const toggles = document.querySelectorAll('#dark-mode-toggle, [data-dark-toggle]');
        toggles.forEach(toggle => {
            toggle.checked = isDark;
        });
    },

    /**
     * Sincronizar con preferencias del sistema
     */
    syncWithSystemPreference() {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            // Solo aplicar si no hay preferencia guardada explícitamente
            if (!localStorage.getItem(this.STORAGE_KEY)) {
                if (e.matches) {
                    this.enableDark();
                } else {
                    this.disableDark();
                }
            }
        });
    },

    /**
     * Obtener estado actual
     */
    isDarkMode() {
        return document.documentElement.classList.contains('dark');
    },

    /**
     * Mostrar mensaje de feedback
     */
    showMessage(message) {
        if (typeof showMessage === 'function') {
            showMessage(message, 'success');
        }
    },

    /**
     * Limpiar configuraciones
     */
    clearSettings() {
        localStorage.removeItem(this.STORAGE_KEY);
    }
};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    TailwindDarkMode.init();
});

// Exportar para uso global
window.TailwindDarkMode = TailwindDarkMode;
