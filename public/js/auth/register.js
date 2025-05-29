/**
 * SmartFood - Registro de Usuario
 * Manejo del formulario de registro con selección de alérgenos
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

    // Configurar toggle de contraseña principal
    if (togglePassword && passwordField && eyeIcon) {
        togglePassword.addEventListener('click', function() {
            togglePasswordVisibility(passwordField, eyeIcon);
        });
    }

    // Configurar toggle de confirmar contraseña
    if (toggleConfirmPassword && confirmPasswordField && eyeConfirmIcon) {
        toggleConfirmPassword.addEventListener('click', function() {
            togglePasswordVisibility(confirmPasswordField, eyeConfirmIcon);
        });
    }

    // Configurar envío del formulario
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }

    /**
     * Alternar visibilidad de contraseña
     * @param {HTMLInputElement} field - Campo de contraseña
     * @param {HTMLElement} icon - Icono del ojo
     */
    function togglePasswordVisibility(field, icon) {
        const type = field.getAttribute('type') === 'password' ? 'text' : 'password';
        field.setAttribute('type', type);

        if (type === 'text') {
            // Icono de ojo tachado (contraseña visible)
            icon.innerHTML = `
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L8.465 8.465m1.413 1.413L8.465 8.465m5.653 5.653l1.413 1.413M15.121 15.121L8.465 8.465m5.653 5.653a3 3 0 01-4.242-4.242m0 0L8.465 8.465" />
            `;
        } else {
            // Icono de ojo normal (contraseña oculta)
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
     * Procesar el registro del usuario
     * @param {Event} e - Evento del formulario
     */
    async function handleRegister(e) {
        e.preventDefault();

        // Recopilar datos del formulario
        const formData = new FormData(registerForm);
        const data = {
            name: formData.get('name'),
            surname: formData.get('surname'),
            email: formData.get('email'),
            password: formData.get('password'),
            password_confirmation: formData.get('password_confirmation'),
            allergens: selectedAllergens.map(a => a.id)
        };

        // Validaciones del cliente
        if (!data.name || !data.surname || !data.email || !data.password || !data.password_confirmation) {
            showMessage('Por favor, completa todos los campos', 'error');
            return;
        }

        if (!isValidEmail(data.email)) {
            showMessage('Por favor, ingresa un email válido', 'error');
            return;
        }

        if (data.password !== data.password_confirmation) {
            showMessage('Las contraseñas no coinciden', 'error');
            return;
        }

        if (data.password.length < 8) {
            showMessage('La contraseña debe tener al menos 8 caracteres', 'error');
            return;
        }

        // Activar estado de carga
        setRegisterLoading(true);

        try {
            // Enviar datos de registro al servidor
            const result = await apiRequest('/api/auth/register', {
                method: 'POST',
                body: JSON.stringify(data)
            });

            if (result.success) {
                showMessage('¡Registro exitoso! Verifica tu email antes de iniciar sesión.', 'success');

                // Redirigir al login después de mostrar el mensaje
                setTimeout(() => {
                    window.location.href = '/login';
                }, 2000);
            } else {
                showMessage(result.message || 'Error al registrarse', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showMessage('Error de conexión. Por favor, inténtalo de nuevo.', 'error');
        } finally {
            setRegisterLoading(false);
        }
    }

    /**
     * Controlar estado de carga del botón de registro
     * @param {boolean} loading - true para mostrar carga
     */
    function setRegisterLoading(loading) {
        if (registerButton && registerButtonText && registerSpinner) {
            registerButton.disabled = loading;
            registerButtonText.textContent = loading ? 'Registrando...' : 'Crear Cuenta';
            registerSpinner.classList.toggle('hidden', !loading);
        }
    }
});
