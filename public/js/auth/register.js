/**
 * SmartFood - Registro de Usuario (Enfoque Híbrido)
 *
 * Este archivo implementa el enfoque híbrido para el formulario de registro:
 * - JavaScript para validación inmediata y mejor UX
 * - Degradación elegante a formularios HTML tradicionales
 * - Soporte completo para accesibilidad
 * - Manejo avanzado del dropdown de alérgenos
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elementos del formulario
    const registerForm = document.getElementById('register-form');
    const passwordField = document.getElementById('password');
    const confirmPasswordField = document.getElementById('password_confirmation');
    const togglePassword = document.getElementById('toggle-password');
    const toggleConfirmPassword = document.getElementById('toggle-confirm-password');
    const eyeIcon = document.getElementById('eye-icon');
    const eyeConfirmIcon = document.getElementById('eye-confirm-icon');
    const registerButton = document.getElementById('register-button');
    const registerButtonText = document.getElementById('register-button-text');
    const registerSpinner = document.getElementById('register-spinner');
    const errorMessage = document.getElementById('error-message');

    // Elementos del dropdown de alérgenos
    const allergensDropdown = document.getElementById('allergens-dropdown');
    const allergensDisplay = document.getElementById('allergens-display');
    const allergensDropdownMenu = document.getElementById('allergens-dropdown-menu');
    const allergensSearch = document.getElementById('allergens-search');
    const allergensCheckboxes = document.querySelectorAll('.allergen-checkbox');

    // Array para almacenar alérgenos seleccionados
    let selectedAllergens = [];

    // Inicializar funcionalidades
    initAllergensDropdown();
    initFieldValidation();

    // Configurar toggle de contraseña principal
    if (togglePassword && passwordField && eyeIcon) {
        togglePassword.addEventListener('click', function() {
            togglePasswordVisibility(passwordField, eyeIcon, togglePassword);
        });
    }

    // Configurar toggle de confirmar contraseña
    if (toggleConfirmPassword && confirmPasswordField && eyeConfirmIcon) {
        toggleConfirmPassword.addEventListener('click', function() {
            togglePasswordVisibility(confirmPasswordField, eyeConfirmIcon, toggleConfirmPassword);
        });
    }

    // Interceptar envío del formulario para AJAX
    if (registerForm) {
        registerForm.addEventListener('submit', handleFormSubmit);
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
        const formData = new FormData(registerForm);
        const registerData = {
            name: formData.get('name'),
            surname: formData.get('surname'),
            email: formData.get('email'),
            password: formData.get('password'),
            password_confirmation: formData.get('password_confirmation'),
            allergens: selectedAllergens.map(a => a.id)
        };

        // Activar estado de carga
        setRegisterLoading(true);

        try {
            console.log('Intentando registro AJAX...');

            // Hacer petición AJAX
            const response = await fetch(registerForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ||
                                   document.querySelector('input[name="_token"]')?.value
                },
                body: JSON.stringify(registerData)
            });

            const result = await response.json();

            if (result.success) {
                console.log('Registro exitoso');
                showMessage('¡Registro exitoso! Bienvenido/a a SmartFood.', 'success');

                // Redirigir después de mostrar mensaje
                setTimeout(() => {
                    window.location.href = result.redirect_url || '/chat';
                }, 1500);

            } else {
                console.log('Error de registro:', result.message);
                showMessage(result.message || 'Error al registrarse', 'error');

                // Mostrar errores de validación específicos
                if (result.errors) {
                    showValidationErrors(result.errors);
                }
            }

        } catch (error) {
            console.error('Error AJAX:', error);

            // En caso de error AJAX, realizar envío tradicional como fallback
            console.log('Fallback: Enviando formulario de manera tradicional');
            setRegisterLoading(false);
            registerForm.submit();
        } finally {
            if (!document.hidden) { // Solo si la página sigue visible
                setRegisterLoading(false);
            }
        }
    }

    /**
     * Validar formulario del lado cliente
     */
    function validateForm() {
        let isValid = true;

        // Validar nombre
        if (!validateNameField()) {
            isValid = false;
        }

        // Validar apellido
        if (!validateSurnameField()) {
            isValid = false;
        }

        // Validar email
        if (!validateEmailField()) {
            isValid = false;
        }

        // Validar contraseña
        if (!validatePasswordField()) {
            isValid = false;
        }

        // Validar confirmación de contraseña
        if (!validatePasswordConfirmationField()) {
            isValid = false;
        }

        return isValid;
    }

    /**
     * Inicializar validación en tiempo real de campos
     */
    function initFieldValidation() {
        const nameField = document.getElementById('name-input');
        const surnameField = document.getElementById('surname-input');
        const emailField = document.getElementById('email');

        if (nameField) {
            nameField.addEventListener('blur', validateNameField);
            nameField.addEventListener('input', () => clearFieldError('name'));
        }

        if (surnameField) {
            surnameField.addEventListener('blur', validateSurnameField);
            surnameField.addEventListener('input', () => clearFieldError('surname'));
        }

        if (emailField) {
            emailField.addEventListener('blur', validateEmailField);
            emailField.addEventListener('input', () => clearFieldError('email'));
        }

        if (passwordField) {
            passwordField.addEventListener('blur', validatePasswordField);
            passwordField.addEventListener('input', () => {
                clearFieldError('password');
                // Re-validar confirmación si ya tiene valor
                if (confirmPasswordField.value) {
                    validatePasswordConfirmationField();
                }
            });
        }

        if (confirmPasswordField) {
            confirmPasswordField.addEventListener('blur', validatePasswordConfirmationField);
            confirmPasswordField.addEventListener('input', () => clearFieldError('password_confirmation'));
        }
    }

    /**
     * Validadores específicos por campo
     */
    function validateNameField() {
        const nameField = document.getElementById('name-input');
        const name = nameField.value.trim();

        if (!name) {
            showFieldError('name', 'El nombre es obligatorio');
            return false;
        }

        clearFieldError('name');
        return true;
    }

    function validateSurnameField() {
        const surnameField = document.getElementById('surname-input');
        const surname = surnameField.value.trim();

        if (!surname) {
            showFieldError('surname', 'Los apellidos son obligatorios');
            return false;
        }

        clearFieldError('surname');
        return true;
    }

    function validateEmailField() {
        const emailField = document.getElementById('email');
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

    function validatePasswordField() {
        const password = passwordField.value;

        if (!password) {
            showFieldError('password', 'La contraseña es obligatoria');
            return false;
        }

        if (password.length < 8) {
            showFieldError('password', 'La contraseña debe tener al menos 8 caracteres');
            return false;
        }

        clearFieldError('password');
        return true;
    }

    function validatePasswordConfirmationField() {
        const password = passwordField.value;
        const passwordConfirmation = confirmPasswordField.value;

        if (!passwordConfirmation) {
            showFieldError('password_confirmation', 'La confirmación de contraseña es obligatoria');
            return false;
        }

        if (password !== passwordConfirmation) {
            showFieldError('password_confirmation', 'Las contraseñas no coinciden');
            return false;
        }

        clearFieldError('password_confirmation');
        return true;
    }

    /**
     * Alternar visibilidad de contraseña
     */
    function togglePasswordVisibility(field, icon, button) {
        const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
        field.setAttribute('type', type);

        // Actualizar aria-label para accesibilidad
        button.setAttribute('aria-label', type === 'text' ? 'Ocultar contraseña' : 'Mostrar contraseña');

        if (type === 'text') {
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.465 8.465m1.413 1.413L8.465 8.465m5.653 5.653l1.413 1.413M15.121 15.121L8.465 8.465m5.653 5.653a3 3 0 01-4.242-4.242m0 0L8.465 8.465" />
            `;
        } else {
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            `;
        }
    }

    /**
     * Inicializar funcionalidad del dropdown de alérgenos
     */
    function initAllergensDropdown() {
        if (!allergensDropdown) return;

        // Cargar alérgenos seleccionados desde el servidor (old values)
        loadSelectedAllergens();

        // Click en el dropdown para abrir/cerrar
        allergensDropdown.addEventListener('click', function(e) {
            e.stopPropagation();
            allergensDropdownMenu.classList.toggle('hidden');
        });

        // Búsqueda en tiempo real de alérgenos
        if (allergensSearch) {
            allergensSearch.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                allergensCheckboxes.forEach(checkbox => {
                    const label = checkbox.closest('label');
                    const text = label.textContent.toLowerCase();
                    label.style.display = text.includes(searchTerm) ? 'flex' : 'none';
                });
            });
        }

        // Manejo de selección de checkboxes
        allergensCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allergenId = this.value;
                const allergenName = this.dataset.name;

                if (this.checked) {
                    selectedAllergens.push({ id: allergenId, name: allergenName });
                } else {
                    selectedAllergens = selectedAllergens.filter(a => a.id !== allergenId);
                }

                updateAllergensDisplay();
            });
        });

        // Cerrar dropdown al hacer click fuera
        document.addEventListener('click', function() {
            allergensDropdownMenu.classList.add('hidden');
        });
    }

    /**
     * Cargar alérgenos seleccionados desde valores old() del servidor
     */
    function loadSelectedAllergens() {
        allergensCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const allergenId = checkbox.value;
                const allergenName = checkbox.dataset.name;
                selectedAllergens.push({ id: allergenId, name: allergenName });
            }
        });
        updateAllergensDisplay();
    }

    /**
     * Actualizar la visualización de alérgenos seleccionados
     */
    function updateAllergensDisplay() {
        if (!allergensDisplay) return;

        if (selectedAllergens.length === 0) {
            allergensDisplay.textContent = 'Selecciona tus alérgenos';
            allergensDisplay.classList.add('text-gray-400');
            allergensDisplay.classList.remove('text-gray-900');
        } else {
            const names = selectedAllergens.map(a => a.name);
            allergensDisplay.textContent = names.length > 3
                ? `${names.slice(0, 3).join(', ')} (+${names.length - 3} más)`
                : names.join(', ');
            allergensDisplay.classList.remove('text-gray-400');
            allergensDisplay.classList.add('text-gray-900');
        }
    }

    /**
     * Mostrar error en campo específico
     */
    function showFieldError(fieldName, message) {
        const field = document.getElementById(fieldName === 'name' ? 'name-input' :
                                            fieldName === 'surname' ? 'surname-input' : fieldName);
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
    function clearFieldError(fieldName) {
        const field = document.getElementById(fieldName === 'name' ? 'name-input' :
                                            fieldName === 'surname' ? 'surname-input' : fieldName);
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

        // Limpiar errores de campos
        ['name', 'surname', 'email', 'password', 'password_confirmation', 'allergens'].forEach(clearFieldError);
    }

    /**
     * Controlar estado de carga del botón
     */
    function setRegisterLoading(loading) {
        if (registerButton && registerButtonText && registerSpinner) {
            registerButton.disabled = loading;
            registerButtonText.textContent = loading ? 'Registrando...' : 'Crear Cuenta';
            registerSpinner.classList.toggle('hidden', !loading);
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

    console.log('Sistema de registro híbrido inicializado');
});
