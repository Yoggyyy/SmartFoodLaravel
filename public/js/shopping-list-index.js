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
 * Verificar si no quedan listas para mostrar el estado vacío
 */
function checkIfNoListsRemaining() {
    const listItems = document.querySelectorAll('[data-list-id]');
    const visibleItems = Array.from(listItems).filter(item => item.style.display !== 'none');

    const emptyState = document.querySelector('.empty-state');
    const listsContainer = document.querySelector('.lists-container');

    if (visibleItems.length === 0 && emptyState && listsContainer) {
        emptyState.style.display = 'block';
        listsContainer.style.display = 'none';
    } else if (emptyState && listsContainer) {
        emptyState.style.display = 'none';
        listsContainer.style.display = 'block';
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
