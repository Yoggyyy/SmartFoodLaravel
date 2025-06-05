/**
 * SmartFood - Login (Enfoque Híbrido)
 *
 * Este archivo implementa el enfoque híbrido para el formulario de login:
 * - JavaScript para validación inmediata y mejor UX
 * - Degradación elegante a formularios HTML tradicionales
 * - Soporte completo para accesibilidad
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
    const errorMessage = document.getElementById('error-message');
    const verificationMessage = document.getElementById('verification-message');
    const verifyDevBtn = document.getElementById('verify-dev-btn');

    // Funcionalidad para mostrar/ocultar contraseña
    if (togglePassword && passwordField && eyeIcon) {
        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);

            // Actualizar aria-label para accesibilidad
            togglePassword.setAttribute('aria-label', type === 'text' ? 'Ocultar contraseña' : 'Mostrar contraseña');

            // Cambiar icono según el estado
            if (type === 'text') {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.465 8.465m1.413 1.413L8.465 8.465m5.653 5.653l1.413 1.413M15.121 15.121L8.465 8.465m5.653 5.653a3 3 0 01-4.242-4.242m0 0L8.465 8.465" />
                `;
            } else {
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                `;
            }
        });
    }

    // Validación en tiempo real de email
    const emailField = document.getElementById('email');
    if (emailField) {
        emailField.addEventListener('blur', validateEmailField);
        emailField.addEventListener('input', clearFieldError);
    }

    // Validación en tiempo real de contraseña
    if (passwordField) {
        passwordField.addEventListener('input', clearFieldError);
    }

    // Interceptar envío del formulario para AJAX
    if (loginForm) {
        loginForm.addEventListener('submit', handleFormSubmit);
    }

    // Botón de verificación automática para desarrollo
    if (verifyDevBtn) {
        verifyDevBtn.addEventListener('click', handleDevVerification);
    }

    /**
     * Manejar envío del formulario (Enfoque Híbrido)
     * Intenta envío AJAX, pero permite degradación a formulario tradicional
     */
    async function handleFormSubmit(e) {
        // Si no hay soporte para fetch, permitir envío tradicional
        if (!window.fetch) {
            console.log('Navegador sin soporte para fetch, usando envío tradicional');
            return; // Permitir envío normal del formulario
        }

        e.preventDefault();

        // Limpiar mensajes previos
        clearMessages();

        // Validación del lado cliente
        if (!validateForm()) {
            return;
        }

        // Obtener datos del formulario
        const formData = new FormData(loginForm);
        const loginData = {
            email: formData.get('email'),
            password: formData.get('password'),
            remember: formData.get('remember') === '1'
        };

        // Activar estado de carga
        setLoginLoading(true);

        try {
            console.log('Intentando login AJAX...');

            // Hacer petición AJAX
            const response = await fetch(loginForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                   document.querySelector('input[name="_token"]')?.value
                },
                body: JSON.stringify(loginData)
            });

            const result = await response.json();

            if (result.success) {
                console.log('Login exitoso');
                showMessage('¡Inicio de sesión exitoso!', 'success');

                // Redirigir después de mostrar mensaje
                setTimeout(() => {
                    window.location.href = result.redirect_url || '/chat';
                }, 1000);

            } /* TEMPORAL: Verificación desactivada
            else if (result.needs_verification) {
                console.log('Necesita verificación');
                showVerificationMessage(result.user_id);
            } */ else {
                console.log('Error de login:', result.message);
                showMessage(result.message || 'Error al iniciar sesión', 'error');

                // Mostrar errores de validación específicos
                if (result.errors) {
                    showValidationErrors(result.errors);
                }
            }

        } catch (error) {
            console.error('Error AJAX:', error);

            // En caso de error AJAX, realizar envío tradicional como fallback
            console.log('Fallback: Enviando formulario de manera tradicional');
            setLoginLoading(false);
            loginForm.submit();
        } finally {
            if (!document.hidden) { // Solo si la página sigue visible
                setLoginLoading(false);
            }
        }
    }

    /**
     * Validar formulario del lado cliente
     */
    function validateForm() {
        let isValid = true;

        // Validar email
        if (!validateEmailField()) {
            isValid = false;
        }

        // Validar contraseña
        if (!passwordField.value.trim()) {
            showFieldError('password', 'La contraseña es obligatoria');
            isValid = false;
        }

        return isValid;
    }

    /**
     * Validar campo de email
     */
    function validateEmailField() {
        const email = emailField.value.trim();

        if (!email) {
            showFieldError('email', 'El email es obligatorio');
            return false;
        }

        if (!isValidEmail(email)) {
            showFieldError('email', 'El formato del email no es válido');
            return false;
        }

        clearFieldError('email');
        return true;
    }

    /**
     * Mostrar error en campo específico
     */
    function showFieldError(fieldName, message) {
        const field = document.getElementById(fieldName);
        const errorElement = document.getElementById(`${fieldName}-error`);

        if (field) {
            // Actualizar clases para usar Tailwind correcto
            field.classList.remove('border-transparent', 'focus:border-green-600', 'focus:ring-green-600/20');
            field.classList.add('border-red-600', 'focus:border-red-600', 'focus:ring-red-600/20');
            field.setAttribute('aria-invalid', 'true');
        }

        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }
    }

    /**
     * Limpiar error de campo específico
     */
    function clearFieldError(fieldOrEvent) {
        const fieldName = typeof fieldOrEvent === 'string' ? fieldOrEvent : fieldOrEvent.target.name;
        const field = document.getElementById(fieldName);
        const errorElement = document.getElementById(`${fieldName}-error`);

        if (field) {
            // Restaurar clases originales de Tailwind
            field.classList.remove('border-red-600', 'focus:border-red-600', 'focus:ring-red-600/20');
            field.classList.add('border-transparent', 'focus:border-green-600', 'focus:ring-green-600/20');
            field.removeAttribute('aria-invalid');
        }

        if (errorElement) {
            errorElement.classList.add('hidden');
        }
    }

    /**
     * Mostrar errores de validación del servidor
     */
    function showValidationErrors(errors) {
        Object.keys(errors).forEach(field => {
            const messages = Array.isArray(errors[field]) ? errors[field] : [errors[field]];
            showFieldError(field, messages[0]);
        });
    }

    /**
     * Limpiar todos los mensajes de error
     */
    function clearMessages() {
        if (errorMessage) {
            errorMessage.classList.add('hidden');
        }
        if (verificationMessage) {
            verificationMessage.classList.add('hidden');
        }

        // Limpiar errores de campos
        ['email', 'password'].forEach(clearFieldError);
    }

    /**
     * Mostrar mensaje de verificación
     */
    function showVerificationMessage(userId) {
        if (verificationMessage) {
            verificationMessage.classList.remove('hidden');

            // Configurar botón de verificación
            if (verifyDevBtn) {
                verifyDevBtn.setAttribute('data-user-id', userId);
            }
        }
    }

    /**
     * Controlar estado de carga del botón
     */
    function setLoginLoading(loading) {
        if (loginButton && loginButtonText && loginSpinner) {
            loginButton.disabled = loading;
            loginButtonText.textContent = loading ? 'Iniciando sesión...' : 'Iniciar Sesión';
            loginSpinner.classList.toggle('hidden', !loading);
        }
    }

    /**
     * Mostrar mensaje general
     */
    function showMessage(message, type = 'error') {
        if (errorMessage) {
            errorMessage.textContent = message;
            // Usar clases Tailwind correctas
            errorMessage.className = `text-sm ${type === 'error' ? 'text-red-600' : 'text-green-700'}`;
            errorMessage.classList.remove('hidden');
        }
    }

    /**
     * Validar formato de email
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Manejar verificación automática en desarrollo
     */
    async function handleDevVerification() {
        const userId = verifyDevBtn.getAttribute('data-user-id');
        if (!userId) return;

        try {
            const user = await getCurrentUser(); // De user-utils.js
            if (user && user.email) {
                window.location.href = `/dev/verify-email/${encodeURIComponent(user.email)}`;
            }
        } catch (error) {
            console.error('Error obteniendo usuario:', error);
        }
    }

    console.log('Sistema de login híbrido inicializado');
});
