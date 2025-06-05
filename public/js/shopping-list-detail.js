/**
 * Shopping List Detail Manager - Gestión de productos en vista de detalles
 * Maneja checkboxes, progreso, filtrado y actualización de estados
 * Versión corregida SIN bucles infinitos
 */

// Variables globales
let currentDetailListId = null;

/**
 * Inicializar la gestión de la vista de detalles
 * @param {number} listId - ID de la lista actual
 */
function initializeShoppingListDetail(listId) {
    currentDetailListId = listId;
    initializeDetailFeatures();
}

/**
 * Inicializar funcionalidades específicas de la vista de detalles
 */
function initializeDetailFeatures() {
    // Aplicar estilos iniciales a productos
    applyInitialProductStyles();

    // Configurar progreso inicial
    updateDetailProgress();

    // Configurar filtros de búsqueda
    const searchInput = document.getElementById('search-products');
    const categoryFilter = document.getElementById('filter-category');

    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterProducts);
    }

    // Actualizar botón "Seleccionar todo"
    updateSelectAllButton();

    console.log('✅ Funcionalidades de vista de detalles inicializadas');
}

/**
 * Aplicar estilos iniciales a productos ya completados
 */
function applyInitialProductStyles() {
    const checkboxes = document.querySelectorAll('#products-container input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            const row = checkbox.closest('tr');
            row.classList.add('opacity-60', 'line-through');
        }
    });
}

// =============================================================================
// GESTIÓN DE FILTRADO Y BÚSQUEDA
// =============================================================================

/**
 * Filtrar productos por búsqueda y categoría
 */
function filterProducts() {
    const searchTerm = document.getElementById('search-products').value.toLowerCase();
    const selectedCategory = document.getElementById('filter-category').value;
    const productRows = document.querySelectorAll('.product-row');

    productRows.forEach(row => {
        const productName = row.querySelector('.product-name').textContent.toLowerCase();
        const productCategory = row.dataset.category || '';

        const matchesSearch = productName.includes(searchTerm);
        const matchesCategory = !selectedCategory || productCategory === selectedCategory;

        if (matchesSearch && matchesCategory) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// =============================================================================
// GESTIÓN DE CHECKBOXES (CORREGIDO - SIN BUCLES INFINITOS)
// =============================================================================

/**
 * Alternar selección de todos los productos (CORREGIDO)
 */
function toggleSelectAll() {
    const checkboxes = document.querySelectorAll('#products-container input[type="checkbox"]');

    // Verificar si algún checkbox está desmarcado
    const hasUnchecked = Array.from(checkboxes).some(cb => !cb.checked);

    // ARREGLO: No disparar eventos change, cambiar estados directamente
    checkboxes.forEach(checkbox => {
        checkbox.checked = hasUnchecked;

        // Aplicar estilos manualmente sin disparar eventos
        const row = checkbox.closest('tr');
        const productId = row.dataset.productId;

        if (checkbox.checked) {
            row.classList.add('opacity-60', 'line-through');
        } else {
            row.classList.remove('opacity-60', 'line-through');
        }

        // Actualizar BD sin recursión
        updateProductCompletedStatusSafe(productId, checkbox.checked);
    });

    // Actualizar UI una sola vez al final
    updateDetailProgress();
    updateSelectAllButton();
    checkListCompletion();
}

/**
 * Función para alternar producto individual (SIN BUCLES)
 */
function toggleProduct(checkbox) {
    const row = checkbox.closest('tr');
    const productId = row.dataset.productId;

    if (checkbox.checked) {
        row.classList.add('opacity-60', 'line-through');
    } else {
        row.classList.remove('opacity-60', 'line-through');
    }

    // Actualizar estado en la base de datos SIN recursión
    updateProductCompletedStatusSafe(productId, checkbox.checked);

    updateDetailProgress();
    updateSelectAllButton();
    checkListCompletion();
}

/**
 * Actualizar el texto del botón "Seleccionar todo"
 */
function updateSelectAllButton() {
    const checkboxes = document.querySelectorAll('#products-container input[type="checkbox"]');
    const selectAllBtn = document.getElementById('select-all-btn');

    if (!selectAllBtn || checkboxes.length === 0) return;

    const allChecked = Array.from(checkboxes).every(cb => cb.checked);

    if (allChecked) {
        selectAllBtn.textContent = 'Deseleccionar todo';
        selectAllBtn.classList.remove('text-gray-600', 'hover:text-gray-900');
        selectAllBtn.classList.add('text-green-600', 'hover:text-green-800');
    } else {
        selectAllBtn.textContent = 'Seleccionar todo';
        selectAllBtn.classList.remove('text-green-600', 'hover:text-green-800');
        selectAllBtn.classList.add('text-gray-600', 'hover:text-gray-900');
    }
}

// =============================================================================
// ACTUALIZACIÓN DE ESTADO EN BD (CORREGIDO - SIN BUCLES INFINITOS)
// =============================================================================

/**
 * Actualizar estado completado SIN BUCLES INFINITOS
 */
async function updateProductCompletedStatusSafe(productId, completed) {
    // Validar que tenemos los datos necesarios
    if (!productId || !currentDetailListId) {
        console.error('Faltan datos necesarios: productId o currentDetailListId');
        return;
    }

    try {
        const response = await fetch(`/listas/${currentDetailListId}/productos/${productId}/completed`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ completed: completed })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            console.error('Error al actualizar estado del producto:', result.message || response.statusText);

            // ARREGLO: Solo mostrar error, NO revertir automáticamente
            if (typeof showDetailNotification === 'function') {
                showDetailNotification('Error al actualizar producto. Intenta nuevamente.', 'error');
            }

            // OPCIONAL: Revertir estado visual pero SIN llamar toggleProduct()
            const checkbox = document.querySelector(`tr[data-product-id="${productId}"] input[type="checkbox"]`);
            if (checkbox) {
                checkbox.checked = !completed;
                const row = checkbox.closest('tr');
                if (row) {
                    if (completed) {
                        row.classList.remove('opacity-60', 'line-through');
                    } else {
                        row.classList.add('opacity-60', 'line-through');
                    }
                    updateDetailProgress();
                    updateSelectAllButton();
                }
            }
        } else {
            // Éxito silencioso - no mostrar notificación para cada checkbox
            console.log(`Producto ${productId} actualizado correctamente`);
        }
    } catch (error) {
        console.error('Error de conexión al actualizar estado del producto:', error);
        if (typeof showDetailNotification === 'function') {
            showDetailNotification('Error de conexión. Intenta nuevamente.', 'error');
        }

        // Revertir estado visual en caso de error de conexión
        const checkbox = document.querySelector(`tr[data-product-id="${productId}"] input[type="checkbox"]`);
        if (checkbox) {
            checkbox.checked = !completed;
            const row = checkbox.closest('tr');
            if (row) {
                if (completed) {
                    row.classList.remove('opacity-60', 'line-through');
                } else {
                    row.classList.add('opacity-60', 'line-through');
                }
                updateDetailProgress();
                updateSelectAllButton();
            }
        }
    }
}

// =============================================================================
// GESTIÓN DE PROGRESO Y ESTADÍSTICAS
// =============================================================================

/**
 * Actualizar barra de progreso y estadísticas
 */
function updateDetailProgress() {
    const checkboxes = document.querySelectorAll('#products-container input[type="checkbox"]');
    const checkedBoxes = document.querySelectorAll('#products-container input[type="checkbox"]:checked');

    const total = checkboxes.length;
    const completed = checkedBoxes.length;
    const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;

    // Actualizar elementos del progreso
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const progressPercentage = document.getElementById('progress-percentage');

    if (progressBar) {
        progressBar.style.width = percentage + '%';
    }

    if (progressText) {
        progressText.textContent = `${completed}/${total}`;
    }

    if (progressPercentage) {
        progressPercentage.textContent = `${percentage}%`;
    }

    // Actualizar badge de estado dinámicamente
    updateStatusBadge(percentage, total, completed);
}

/**
 * Actualizar el badge de estado de la lista
 */
function updateStatusBadge(percentage, total, completed) {
    const statusBadge = document.getElementById('list-status-badge');
    const statusText = document.getElementById('status-text');
    const statusIcon = document.getElementById('status-icon');

    if (!statusBadge || !statusText || !statusIcon) return;

    if (total === 0) {
        // Lista vacía
        statusBadge.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800 transition-all duration-300';
        statusText.textContent = 'Vacía';
        statusIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>';
    } else if (percentage === 100) {
        // Lista completada al 100%
        statusBadge.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-green-100 text-green-800 transition-all duration-300';
        statusText.textContent = 'Completada';
        statusIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
    } else if (percentage >= 50) {
        // En progreso avanzado
        statusBadge.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-blue-100 text-blue-800 transition-all duration-300';
        statusText.textContent = 'En progreso';
        statusIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>';
    } else if (percentage > 0) {
        // Iniciada pero con poco progreso
        statusBadge.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800 transition-all duration-300';
        statusText.textContent = 'Iniciada';
        statusIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
    } else {
        // Lista activa sin productos completados
        statusBadge.className = 'inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-orange-100 text-orange-800 transition-all duration-300';
        statusText.textContent = 'Activa';
        statusIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
    }
}

/**
 * Verificar si la lista está completada y actualizar estado
 */
function checkListCompletion() {
    const checkboxes = document.querySelectorAll('#products-container input[type="checkbox"]');
    const checkedBoxes = document.querySelectorAll('#products-container input[type="checkbox"]:checked');

    const total = checkboxes.length;
    const completed = checkedBoxes.length;

    // Si todos los productos están marcados, marcar lista como completada
    if (total > 0 && completed === total) {
        updateListStatus('completed');
        showDetailNotification('¡Lista completada! 🎉', 'success');
    } else if (completed === 0) {
        updateListStatus('active');
    }
}

/**
 * Actualizar estado de la lista en el servidor
 */
async function updateListStatus(status) {
    try {
        const response = await fetch(`/listas/${currentDetailListId}/status`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ status: status })
        });

        if (response.ok) {
            console.log(`Lista marcada como ${status}`);
        }
    } catch (error) {
        console.error('Error al actualizar estado de lista:', error);
    }
}

// =============================================================================
// ELIMINACIÓN DE PRODUCTOS
// =============================================================================

/**
 * Eliminar un producto de la lista (función global)
 */
window.deleteProduct = async function(productId) {
    if (!confirm('¿Estás seguro de que quieres eliminar este producto?')) {
        return;
    }

    try {
        const response = await fetch(`/listas/${currentDetailListId}/productos/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        });

        if (response.ok) {
            // Remover fila de la tabla
            const row = document.querySelector(`tr[data-product-id="${productId}"]`);
            if (row) {
                row.remove();
                updateDetailProgress();
                updateSelectAllButton();
            }

            // Mostrar mensaje de éxito
            showDetailNotification('Producto eliminado correctamente', 'success');
        } else {
            showDetailNotification('Error al eliminar el producto', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showDetailNotification('Error al eliminar el producto', 'error');
    }
};

// =============================================================================
// SISTEMA DE NOTIFICACIONES
// =============================================================================

/**
 * Mostrar notificaciones específicas para la vista de detalles
 */
function showDetailNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all transform translate-x-full ${
        type === 'success' ? 'bg-green-600 text-white' :
        type === 'error' ? 'bg-red-600 text-white' :
        'bg-blue-600 text-white'
    }`;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Animación de entrada
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Remover después de 3 segundos
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}
