/**
 * Shopping List Index - Gestión del índice de listas de compra
 * Funciones para la página principal de visualización de listas
 */

/**
 * Inicializar la página de índice de listas
 */
function initializeShoppingListIndex() {
    // Cargar datos del usuario
    loadUserData();

    // Configurar eventos de navegación
    setupNavigationEvents();
}

/**
 * Configurar eventos de navegación y enlaces
 */
function setupNavigationEvents() {
    // Configurar enlaces de navegación si existen
    const navigationLinks = document.querySelectorAll('a[href^="/"]');

    navigationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Agregar efecto visual al hacer clic
            this.style.opacity = '0.7';
            setTimeout(() => {
                this.style.opacity = '1';
            }, 150);
        });
    });
}

/**
 * Confirmar eliminación de lista
 * @param {number} listId - ID de la lista a eliminar
 * @param {string} listName - Nombre de la lista para mostrar en confirmación
 * @returns {boolean} true si el usuario confirma la eliminación
 */
function confirmDeleteList(listId, listName) {
    return confirm(`¿Estás seguro de que quieres eliminar la lista "${listName}"?\n\nEsta acción no se puede deshacer.`);
}

/**
 * Eliminar lista de compra
 * @param {number} listId - ID de la lista a eliminar
 * @param {string} listName - Nombre de la lista
 */
async function deleteShoppingList(listId, listName) {
    // Confirmar eliminación
    if (!confirmDeleteList(listId, listName)) {
        return;
    }

    try {
        // Enviar petición de eliminación
        const response = await fetch(`/listas/${listId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();

        if (result.success) {
            // Eliminar el elemento del DOM
            const listElement = document.querySelector(`[data-list-id="${listId}"]`);
            if (listElement) {
                // Animación de salida
                listElement.style.opacity = '0';
                listElement.style.transform = 'translateX(-100%)';

                // Eliminar después de la animación
                setTimeout(() => {
                    listElement.remove();

                    // Verificar si quedan listas
                    checkIfNoListsRemaining();
                }, 300);
            }

            showMessage('Lista eliminada correctamente', 'success');
        } else {
            showMessage(result.message || 'Error al eliminar la lista', 'error');
        }
    } catch (error) {
        console.error('Error eliminando lista:', error);
        showMessage('Error de conexión al eliminar la lista', 'error');
    }
}

/**
 * Verificar si no quedan listas y mostrar mensaje correspondiente
 */
function checkIfNoListsRemaining() {
    const listsContainer = document.querySelector('.shopping-lists-container');
    const listItems = listsContainer?.querySelectorAll('[data-list-id]');

    if (!listItems || listItems.length === 0) {
        // Mostrar mensaje de "no hay listas"
        const emptyStateHtml = `
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📝</div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No tienes listas de compra</h3>
                <p class="text-gray-500 mb-6">Comienza creando tu primera lista en el chat</p>
                <a href="/chat" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition">
                    <span class="mr-2">💬</span>
                    Ir al Chat
                </a>
            </div>
        `;

        if (listsContainer) {
            listsContainer.innerHTML = emptyStateHtml;
        }
    }
}

/**
 * Duplicar lista de compra
 * @param {number} listId - ID de la lista a duplicar
 * @param {string} listName - Nombre de la lista
 */
async function duplicateShoppingList(listId, listName) {
    try {
        // Aquí podrías implementar la lógica de duplicación
        // Por ahora, simplemente mostramos un mensaje
        showMessage(`Función de duplicar "${listName}" en desarrollo`, 'info');

        // TODO: Implementar duplicación real
        /*
        const response = await fetch(`/listas/${listId}/duplicate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();

        if (result.success) {
            showMessage('Lista duplicada correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(result.message || 'Error al duplicar la lista', 'error');
        }
        */
    } catch (error) {
        console.error('Error duplicando lista:', error);
        showMessage('Error al duplicar la lista', 'error');
    }
}

/**
 * Filtrar listas por texto de búsqueda
 * @param {string} searchText - Texto a buscar
 */
function filterLists(searchText) {
    const listItems = document.querySelectorAll('[data-list-id]');
    const searchLower = searchText.toLowerCase();

    listItems.forEach(item => {
        const listName = item.querySelector('h3')?.textContent.toLowerCase() || '';
        const conversationName = item.querySelector('.conversation-name')?.textContent.toLowerCase() || '';

        // Mostrar/ocultar según coincidencia
        if (listName.includes(searchLower) || conversationName.includes(searchLower)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Configurar búsqueda en tiempo real
 */
function setupSearchFunctionality() {
    const searchInput = document.getElementById('search-lists');

    if (searchInput) {
        // Búsqueda en tiempo real con debounce
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                filterLists(this.value);
            }, 300);
        });

        // Limpiar búsqueda con Escape
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filterLists('');
            }
        });
    }
}

/**
 * Animar entrada de elementos
 */
function animateListItems() {
    const listItems = document.querySelectorAll('[data-list-id]');

    listItems.forEach((item, index) => {
        // Ocultar inicialmente
        item.style.opacity = '0';
        item.style.transform = 'translateY(20px)';

        // Animar entrada con delay escalonado
        setTimeout(() => {
            item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            item.style.opacity = '1';
            item.style.transform = 'translateY(0)';
        }, index * 50);
    });
}

/**
 * Funciones para modal de nueva lista
 */
function openNewListModal() {
    const modal = document.getElementById('newListModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeNewListModal() {
    const modal = document.getElementById('newListModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    // Limpiar formulario
    document.getElementById('newListForm').reset();
}

async function createList() {
    const form = document.getElementById('newListForm');
    const formData = new FormData(form);

    const data = {
        name: formData.get('name'),
        budget: formData.get('budget') || null,
        supermarket_id: formData.get('supermarket_id') || null
    };

    try {
        const response = await fetch('/listas', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showMessage('Lista creada correctamente', 'success');
            closeNewListModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            // Manejar errores de validación
            if (result.errors) {
                const errorMessages = Object.values(result.errors).flat();
                showMessage(errorMessages.join(', '), 'error');
            } else {
                showMessage(result.message || 'Error al crear la lista', 'error');
            }
        }
    } catch (error) {
        console.error('Error creando lista:', error);
        showMessage('Error de conexión al crear la lista', 'error');
    }
}

/**
 * Funciones para modal de supermercado
 */
function openSupermarketModal() {
    const modal = document.getElementById('supermarketModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeSupermarketModal() {
    const modal = document.getElementById('supermarketModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function updateSupermarket() {
    const select = document.getElementById('modalSupermarketSelect');
    const supermarketId = select.value;

    // Esta función necesitará el ID de la lista que se está editando
    // Por ahora solo muestra un mensaje
    showMessage('Función de cambiar supermercado en desarrollo', 'info');
    closeSupermarketModal();
}

/**
 * Variables globales para modales
 */
let currentDeletingId = null;

/**
 * Funciones para modal de eliminación
 */
function deleteList(id) {
    currentDeletingId = id;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    currentDeletingId = null;
}

async function confirmDelete() {
    if (!currentDeletingId) return;

    try {
        const response = await fetch(`/listas/${currentDeletingId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();

        if (result.success) {
            showMessage('Lista eliminada correctamente', 'success');
            closeDeleteModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showMessage(result.message || 'Error al eliminar la lista', 'error');
            closeDeleteModal();
        }
    } catch (error) {
        console.error('Error eliminando lista:', error);
        showMessage('Error de conexión al eliminar la lista', 'error');
        closeDeleteModal();
    }
}

/**
 * Función para ver detalles de lista
 */
function viewList(id) {
    window.location.href = `/listas/${id}`;
}

// Inicializar cuando se carga el DOM
document.addEventListener('DOMContentLoaded', function() {
    initializeShoppingListIndex();
    setupSearchFunctionality();
    animateListItems();
});
