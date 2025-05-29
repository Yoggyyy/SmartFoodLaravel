/**
 * SmartFood - Gestión de Perfil
 * Manejo del perfil de usuario, edición y cambio de contraseña
 */

// Variable global para almacenar datos del usuario actual
let currentUser = null;

document.addEventListener('DOMContentLoaded', function() {
    // Cargar datos del perfil al iniciar
    loadUserProfile();

    // Configurar eventos de la página
    document.getElementById('edit-profile-btn').addEventListener('click', toggleEditMode);
    document.getElementById('save-profile-btn').addEventListener('click', saveProfile);
    document.getElementById('cancel-edit-btn').addEventListener('click', cancelEdit);
    document.getElementById('change-password-toggle').addEventListener('click', togglePasswordSection);
    document.getElementById('change-password-btn').addEventListener('click', changePassword);
});

/**
 * Cargar datos del perfil del usuario desde el servidor
 */
async function loadUserProfile() {
    try {
        const result = await apiRequest('/user/me');

        if (result.success) {
            currentUser = result.data;
            updateProfileUI(currentUser);
        } else {
            showMessage('Error al cargar el perfil', 'error');
        }
    } catch (error) {
        console.error('Error loading profile:', error);
        showMessage('Error de conexión al cargar el perfil', 'error');
    }
}

/**
 * Actualizar la interfaz con los datos del usuario
 * @param {Object} user - Datos del usuario
 */
function updateProfileUI(user) {
    // Avatar con iniciales en el sidebar y perfil
    const avatar = generateAvatar(user.name, user.surname);
    document.getElementById('user-avatar').textContent = avatar;
    document.getElementById('profile-avatar').textContent = avatar;

    // Información básica en modo visualización
    document.getElementById('display-name').textContent = user.name;
    document.getElementById('display-surname').textContent = user.surname;
    document.getElementById('display-email').textContent = user.email;

    // Mostrar alérgenos o mensaje por defecto
    const allergensText = user.allergens && user.allergens.length > 0
        ? user.allergens.map(a => a.name_allergen).join(', ')
        : 'No tienes alérgenos registrados';
    document.getElementById('display-allergens').textContent = allergensText;

    // Mostrar preferencias o mensaje por defecto
    document.getElementById('display-preferences').textContent = user.preferences || 'No hay preferencias definidas';

    // Llenar campos de edición con datos actuales
    document.getElementById('edit-name').value = user.name;
    document.getElementById('edit-surname').value = user.surname;
    document.getElementById('edit-email').value = user.email;
    document.getElementById('edit-preferences').value = user.preferences || '';
}

/**
 * Cambiar a modo de edición del perfil
 */
function toggleEditMode() {
    const displayElements = document.querySelectorAll('.profile-display');
    const editElements = document.querySelectorAll('.profile-edit');
    const editBtn = document.getElementById('edit-profile-btn');
    const actionButtons = document.getElementById('profile-action-buttons');

    // Ocultar elementos de visualización y mostrar de edición
    displayElements.forEach(el => el.style.display = 'none');
    editElements.forEach(el => el.style.display = 'flex');
    editBtn.classList.add('hidden');
    actionButtons.classList.remove('hidden');
}

/**
 * Cancelar edición y volver al modo visualización
 */
function cancelEdit() {
    const displayElements = document.querySelectorAll('.profile-display');
    const editElements = document.querySelectorAll('.profile-edit');
    const editBtn = document.getElementById('edit-profile-btn');
    const actionButtons = document.getElementById('profile-action-buttons');

    // Mostrar elementos de visualización y ocultar de edición
    displayElements.forEach(el => el.style.display = 'flex');
    editElements.forEach(el => el.style.display = 'none');
    editBtn.classList.remove('hidden');
    actionButtons.classList.add('hidden');

    // Restaurar valores originales en los campos
    if (currentUser) {
        updateProfileUI(currentUser);
    }
}

/**
 * Guardar cambios del perfil en el servidor
 */
async function saveProfile() {
    const saveBtn = document.getElementById('save-profile-btn');
    const originalText = saveBtn.textContent;

    // Activar estado de carga
    saveBtn.disabled = true;
    saveBtn.textContent = 'Guardando...';

    try {
        // Recopilar datos del formulario
        const formData = {
            name: document.getElementById('edit-name').value.trim(),
            surname: document.getElementById('edit-surname').value.trim(),
            email: document.getElementById('edit-email').value.trim(),
            preferences: document.getElementById('edit-preferences').value.trim()
        };

        // Validaciones del cliente
        if (!formData.name || !formData.surname || !formData.email) {
            showMessage('Por favor, completa todos los campos obligatorios', 'error');
            return;
        }

        if (!isValidEmail(formData.email)) {
            showMessage('Por favor, ingresa un email válido', 'error');
            return;
        }

        // Enviar actualización al servidor
        const result = await apiRequest('/user/update-profile', {
            method: 'PUT',
            body: JSON.stringify(formData)
        });

        if (result.success) {
            // Actualizar datos locales y UI
            currentUser = { ...currentUser, ...formData };
            updateProfileUI(currentUser);
            cancelEdit();
            showMessage('Perfil actualizado exitosamente', 'success');
        } else {
            showMessage(result.message || 'Error al actualizar el perfil', 'error');
        }

    } catch (error) {
        console.error('Error saving profile:', error);
        showMessage('Error de conexión al guardar el perfil', 'error');
    } finally {
        // Restaurar estado del botón
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    }
}

/**
 * Mostrar/ocultar sección de cambio de contraseña
 */
function togglePasswordSection() {
    const passwordSection = document.getElementById('password-change-section');
    const toggleBtn = document.getElementById('change-password-toggle');

    if (passwordSection.classList.contains('hidden')) {
        // Mostrar sección de cambio de contraseña
        passwordSection.classList.remove('hidden');
        toggleBtn.textContent = 'Cancelar cambio de contraseña';
    } else {
        // Ocultar sección y limpiar campos
        passwordSection.classList.add('hidden');
        toggleBtn.textContent = 'Cambiar contraseña';
        document.getElementById('current-password').value = '';
        document.getElementById('new-password').value = '';
        document.getElementById('confirm-new-password').value = '';
    }
}

/**
 * Procesar cambio de contraseña del usuario
 */
async function changePassword() {
    const changeBtn = document.getElementById('change-password-btn');
    const originalText = changeBtn.textContent;

    // Activar estado de carga
    changeBtn.disabled = true;
    changeBtn.textContent = 'Cambiando...';

    try {
        // Recopilar datos de contraseñas
        const formData = {
            current_password: document.getElementById('current-password').value,
            password: document.getElementById('new-password').value,
            password_confirmation: document.getElementById('confirm-new-password').value
        };

        // Validaciones del cliente
        if (!formData.current_password || !formData.password || !formData.password_confirmation) {
            showMessage('Por favor, completa todos los campos de contraseña', 'error');
            return;
        }

        if (formData.password !== formData.password_confirmation) {
            showMessage('Las nuevas contraseñas no coinciden', 'error');
            return;
        }

        if (formData.password.length < 8) {
            showMessage('La nueva contraseña debe tener al menos 8 caracteres', 'error');
            return;
        }

        // Enviar cambio de contraseña al servidor
        const result = await apiRequest('/user/change-password', {
            method: 'PUT',
            body: JSON.stringify(formData)
        });

        if (result.success) {
            showMessage('Contraseña cambiada exitosamente', 'success');
            togglePasswordSection(); // Ocultar sección
        } else {
            showMessage(result.message || 'Error al cambiar la contraseña', 'error');
        }

    } catch (error) {
        console.error('Error changing password:', error);
        showMessage('Error de conexión al cambiar la contraseña', 'error');
    } finally {
        // Restaurar estado del botón
        changeBtn.disabled = false;
        changeBtn.textContent = originalText;
    }
}
