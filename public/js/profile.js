/**
 * SmartFood - Gestión de Perfil
 * Manejo del perfil de usuario, edición y cambio de contraseña
 */

// Estado de la aplicación
let isEditMode = false;
let userData = {};
let statsLoaded = false; // Control para evitar llamadas duplicadas

// Inicialización
document.addEventListener('DOMContentLoaded', function() {
    loadUserData();
    setupEventListeners();
});

// Configurar event listeners
function setupEventListeners() {
    // Botones de edición
    document.getElementById('edit-profile-btn').addEventListener('click', enterEditMode);
    document.getElementById('cancel-edit-btn').addEventListener('click', exitEditMode);
    document.getElementById('cancel-changes-btn').addEventListener('click', exitEditMode);
    document.getElementById('save-profile-btn').addEventListener('click', saveProfile);

    // Cambio de contraseña
    document.getElementById('change-password-toggle').addEventListener('click', togglePasswordSection);
    document.getElementById('save-password-btn').addEventListener('click', changePassword);
    document.getElementById('cancel-password-btn').addEventListener('click', hidePasswordSection);
}

// Cargar datos del usuario
async function loadUserData() {
    try {
        const response = await fetch('/user/me', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });

        if (response.ok) {
            const data = await response.json();
            userData = data.data || data.user;
            populateUserData();
            loadAccountInfo();
        }
    } catch (error) {
        console.error('Error loading user data:', error);
    }
}

// Cargar información de cuenta (estadísticas)
async function loadAccountInfo() {
    console.log('🔍 Intentando cargar estadísticas del usuario...');
    try {
        const response = await fetch('/user/stats', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });

        console.log('📊 Respuesta del endpoint /user/stats:', response.status);

        if (response.ok) {
            const stats = await response.json();
            console.log('✅ Estadísticas recibidas:', stats);
            populateAccountInfo(stats);
            statsLoaded = true; // Marcar que las estadísticas se cargaron exitosamente
        } else {
            console.log('❌ Endpoint /user/stats falló, usando fallback básico');
            // Si no existe el endpoint, usar datos básicos del usuario
            populateAccountInfoBasic();
        }
    } catch (error) {
        console.error('💥 Error loading account stats:', error);
        populateAccountInfoBasic();
    }
}

// Poblar información de cuenta con datos del servidor
function populateAccountInfo(stats) {
    console.log('✅ Aplicando estadísticas recibidas del servidor:', stats);

    // Fecha de miembro desde
    const memberSince = document.getElementById('member-since');
    if (stats.data && stats.data.member_since) {
        const date = new Date(stats.data.member_since);
        const options = { year: 'numeric', month: 'long' };
        memberSince.textContent = date.toLocaleDateString('es-ES', options);
        console.log('📅 Miembro desde (stats):', memberSince.textContent);
    } else if (userData && userData.created_at) {
        const date = new Date(userData.created_at);
        const options = { year: 'numeric', month: 'long' };
        memberSince.textContent = date.toLocaleDateString('es-ES', options);
        console.log('📅 Miembro desde (userData):', memberSince.textContent);
    }

    // Listas creadas
    const listsCount = document.getElementById('lists-count');
    const listsNumber = stats.data ? stats.data.lists_count : stats.lists_count;
    listsCount.textContent = `${listsNumber || 0} listas`;
    console.log('📋 Listas actualizadas desde stats:', listsCount.textContent);

    // Última actividad
    const lastActivity = document.getElementById('last-activity');
    const lastActivityData = stats.data ? stats.data.last_activity : stats.last_activity;
    if (lastActivityData) {
        const activityDate = new Date(lastActivityData);
        const now = new Date();
        const diffTime = Math.abs(now - activityDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays === 1) {
            lastActivity.textContent = 'Hace 1 día';
        } else if (diffDays < 7) {
            lastActivity.textContent = `Hace ${diffDays} días`;
        } else {
            lastActivity.textContent = activityDate.toLocaleDateString('es-ES');
        }
        console.log('⏰ Última actividad (stats):', lastActivity.textContent);
    } else {
        lastActivity.textContent = 'Ahora mismo';
        console.log('⏰ Última actividad por defecto');
    }
}

// Poblar información básica si no hay endpoint de stats
function populateAccountInfoBasic() {
    console.log('📊 Usando datos básicos como fallback...');

    // Usar created_at del usuario para miembro desde
    const memberSince = document.getElementById('member-since');
    if (userData && userData.created_at) {
        const date = new Date(userData.created_at);
        const options = { year: 'numeric', month: 'long' };
        memberSince.textContent = date.toLocaleDateString('es-ES', options);
        console.log('📅 Miembro desde:', memberSince.textContent);
    } else {
        memberSince.textContent = 'Enero 2024';
        console.log('📅 Usando fecha por defecto');
    }

    // Para listas y actividad, intentar cargar por separado SOLO si las estadísticas no se cargaron
    if (!statsLoaded) {
        console.log('📋 Las estadísticas no se cargaron, intentando cargar listas por separado...');
        loadListsCount();
    } else {
        console.log('📋 Las estadísticas ya se cargaron, saltando loadListsCount()');
    }

    const lastActivity = document.getElementById('last-activity');

    // Usar updated_at del usuario si está disponible
    if (userData && userData.updated_at) {
        const lastUpdate = new Date(userData.updated_at);
        const now = new Date();
        const diffTime = Math.abs(now - lastUpdate);
        const diffHours = Math.ceil(diffTime / (1000 * 60 * 60));

        if (diffHours < 24) {
            lastActivity.textContent = `Hace ${diffHours} horas`;
        } else {
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            lastActivity.textContent = `Hace ${diffDays} días`;
        }
        console.log('⏰ Última actividad calculada:', lastActivity.textContent);
    } else {
        lastActivity.textContent = 'Hace unas horas';
        console.log('⏰ Usando actividad por defecto');
    }
}

// Cargar cantidad de listas por separado
async function loadListsCount() {
    console.log('📋 Intentando cargar cantidad de listas...');
    try {
        const response = await fetch('/listas/grouped', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        });

        console.log('📋 Respuesta del endpoint /listas/grouped:', response.status);

        if (response.ok) {
            const listsData = await response.json();
            console.log('📋 Datos de listas recibidos:', listsData);
            const listsCount = document.getElementById('lists-count');

            // Si es una respuesta con datos agrupados
            if (listsData.success && listsData.data && Array.isArray(listsData.data)) {
                // Contar el total de listas en todos los grupos
                let totalLists = 0;
                listsData.data.forEach(group => {
                    if (group.lists && Array.isArray(group.lists)) {
                        totalLists += group.lists.length;
                    }
                });
                console.log('📋 Total de listas calculado:', totalLists);
                listsCount.textContent = `${totalLists} listas`;
            } else {
                console.log('📋 No se encontraron listas en la respuesta');
                listsCount.textContent = '0 listas';
            }
        } else {
            console.log('❌ Error al cargar listas, respuesta no OK');
            const listsCount = document.getElementById('lists-count');
            listsCount.textContent = '0 listas';
        }
    } catch (error) {
        console.error('💥 Error loading lists count:', error);
        const listsCount = document.getElementById('lists-count');
        listsCount.textContent = '0 listas';
    }
}

// Poblar datos en la interfaz
function populateUserData() {
    if (!userData) return;

    // Actualizar información básica
    document.getElementById('display-name').textContent = userData.name || 'Jordi';
    document.getElementById('display-surname').textContent = userData.surname || 'Santos';
    document.getElementById('display-email').textContent = userData.email || 'jordi.s1511@gmail.com';

    // Actualizar campos de edición
    document.getElementById('edit-name').value = userData.name || 'Jordi';
    document.getElementById('edit-surname').value = userData.surname || 'Santos';

    // Actualizar email de solo lectura
    document.getElementById('readonly-email').textContent = userData.email || 'jordi.s1511@gmail.com';

    // Avatar principal
    const avatar = document.getElementById('profile-avatar');
    if (avatar && userData.name) {
        avatar.textContent = userData.name.charAt(0).toUpperCase();
    }

    // Avatar del sidebar
    const sidebarAvatar = document.getElementById('user-avatar-sidebar');
    if (sidebarAvatar && userData.name) {
        sidebarAvatar.textContent = userData.name.charAt(0).toUpperCase();
    }
}

// Entrar en modo edición
function enterEditMode() {
    isEditMode = true;
    document.body.classList.add('edit-mode');
}

// Salir del modo edición
function exitEditMode() {
    isEditMode = false;
    document.body.classList.remove('edit-mode');
    populateUserData(); // Restaurar datos originales
}

// Guardar perfil
async function saveProfile() {
    const profileData = {
        name: document.getElementById('edit-name').value,
        surname: document.getElementById('edit-surname').value,
        allergens: getAllergens()
        };

    try {
        const response = await fetch('/user/update-profile', {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(profileData)
        });

        if (response.ok) {
            const result = await response.json();
            userData = { ...userData, ...profileData };
            populateUserData();
            exitEditMode();
            showMessage('Perfil actualizado correctamente', 'success');
        } else {
            showMessage('Error al actualizar el perfil', 'error');
        }
    } catch (error) {
        console.error('Error saving profile:', error);
        showMessage('Error al guardar los cambios', 'error');
    }
}

// Gestión de alérgenos
function addAllergen() {
    const input = document.getElementById('new-allergen-input');
    const allergen = input.value.trim();

    if (allergen) {
        const allergensDisplay = document.getElementById('allergens-display');
        const allergenTag = document.createElement('div');
        allergenTag.className = 'inline-flex items-center gap-2 bg-red-600 text-white px-4 py-2 rounded-full text-sm m-1';
        allergenTag.innerHTML = `
            ${allergen}
            <span class="edit-mode-content cursor-pointer bg-white bg-opacity-20 hover:bg-opacity-30 rounded-full w-5 h-5 flex items-center justify-center text-xs transition-colors" onclick="removeAllergen(this)">✕</span>
        `;
        allergensDisplay.appendChild(allergenTag);
        input.value = '';
    }
}

function removeAllergen(button) {
    button.parentElement.remove();
}

function handleAllergenKeyPress(event) {
    if (event.key === 'Enter') {
        addAllergen();
    }
}

function getAllergens() {
    const allergenTags = document.querySelectorAll('#allergens-display > div');
    return Array.from(allergenTags).map(tag =>
        tag.textContent.replace('✕', '').trim()
    );
}

// Gestión de contraseña
function togglePasswordSection() {
    const section = document.getElementById('password-change-section');
    section.classList.toggle('hidden');
}

function hidePasswordSection() {
    document.getElementById('password-change-section').classList.add('hidden');
    // Limpiar campos
        document.getElementById('current-password').value = '';
        document.getElementById('new-password').value = '';
        document.getElementById('confirm-new-password').value = '';
}

async function changePassword() {
    const currentPassword = document.getElementById('current-password').value;
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-new-password').value;

    if (newPassword !== confirmPassword) {
        showMessage('Las contraseñas no coinciden', 'error');
            return;
        }

    if (newPassword.length < 8) {
        showMessage('La contraseña debe tener al menos 8 caracteres', 'error');
            return;
        }

    try {
        const response = await fetch('/user/change-password', {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                current_password: currentPassword,
                password: newPassword,
                password_confirmation: confirmPassword
            })
        });

        if (response.ok) {
            hidePasswordSection();
            showMessage('Contraseña actualizada correctamente', 'success');
        } else {
            const errorData = await response.json();
            showMessage(errorData.message || 'Error al cambiar la contraseña', 'error');
        }
    } catch (error) {
        console.error('Error changing password:', error);
        showMessage('Error al cambiar la contraseña', 'error');
    }
}

// Función para mostrar mensajes
function showMessage(message, type) {
    // Crear elemento de mensaje
    const messageDiv = document.createElement('div');
    messageDiv.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all transform translate-x-full ${
        type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'
    }`;
    messageDiv.textContent = message;

    document.body.appendChild(messageDiv);

    // Animación de entrada
    setTimeout(() => {
        messageDiv.classList.remove('translate-x-full');
    }, 100);

    // Remover después de 3 segundos
    setTimeout(() => {
        messageDiv.classList.add('translate-x-full');
        setTimeout(() => {
            document.body.removeChild(messageDiv);
        }, 300);
    }, 3000);
}

// Función para logout
async function logout() {
    try {
        const response = await fetch('/logout', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        });

        if (response.ok) {
            window.location.href = '/login';
        }
    } catch (error) {
        console.error('Error during logout:', error);
    }
}
