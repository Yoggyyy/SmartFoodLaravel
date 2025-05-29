/**
 * SmartFood - Login
 * Manejo del formulario de inicio de sesión
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const loginForm = document.getElementById('login-form');
    const passwordField = document.getElementById('password');
    const togglePassword = document.getElementById('toggle-password');
    const eyeIcon = document.getElementById('eye-icon');
    const loginButton = document.getElementById('login-button');
    const loginButtonText = document.getElementById('login-button-text');
    const loginSpinner = document.getElementById('login-spinner');

    // Funcionalidad para mostrar/ocultar contraseña
    if (togglePassword && passwordField && eyeIcon) {
        togglePassword.addEventListener('click', function() {
            // Cambiar tipo de input
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Cambiar icono según el estado
            if (type === 'text') {
                // Icono de ojo tachado (contraseña visible)
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.465 8.465m1.413 1.413L8.465 8.465m5.653 5.653l1.413 1.413M15.121 15.121L8.465 8.465m5.653 5.653a3 3 0 01-4.242-4.242m0 0L8.465 8.465" />
                `;
            } else {
                // Icono de ojo normal (contraseña oculta)
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        });
    }

    // Configurar evento de envío del formulario
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }

    /**
     * Procesar el login del usuario
     * @param {Event} e - Evento del formulario
     */
    async function handleLogin(e) {
        e.preventDefault();

        // Obtener datos del formulario
        const formData = new FormData(loginForm);
        const email = formData.get('email');
        const password = formData.get('password');

        // Validaciones básicas
        if (!email || !password) {
            showMessage('Por favor, completa todos los campos', 'error');
            return;
        }

        if (!isValidEmail(email)) {
            showMessage('Por favor, ingresa un email válido', 'error');
            return;
        }

        // Activar estado de carga
        setLoginLoading(true);

        try {
            console.log('Intentando iniciar sesión...');

            // Enviar credenciales al servidor
            const result = await apiRequest('/login', {
                method: 'POST',
                body: JSON.stringify({ email, password })
            });

            console.log('Respuesta recibida:', result);

            if (result.success) {
                console.log('Login exitoso');
                // No guardamos token para rutas web, se usa sesión
                showMessage('¡Inicio de sesión exitoso!', 'success');

                // Redirigir al chat después de 1 segundo
                setTimeout(() => {
                    window.location.href = '/chat';
                }, 1000);
            } else {
                showMessage(result.message || 'Error al iniciar sesión', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error de conexión. Por favor, inténtalo de nuevo.', 'error');
        } finally {
            setLoginLoading(false);
        }
    }

    /**
     * Controlar estado de carga del botón de login
     * @param {boolean} loading - true para mostrar carga
     */
    function setLoginLoading(loading) {
        if (loginButton && loginButtonText && loginSpinner) {
            loginButton.disabled = loading;
            loginButtonText.textContent = loading ? 'Iniciando sesión...' : 'Iniciar Sesión';
            loginSpinner.classList.toggle('hidden', !loading);
        }
    }

    // Botones de login social (placeholder)
    document.querySelectorAll('[data-social-login]').forEach(button => {
        button.addEventListener('click', function() {
            const provider = this.dataset.socialLogin;
            showMessage(`Función de ${provider} no implementada aún`, 'error');
        });
    });
});
