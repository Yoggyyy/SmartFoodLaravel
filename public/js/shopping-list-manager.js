/**
 * Shopping List Manager - Gestión de productos en listas de compra
 * Maneja las operaciones CRUD (crear, leer, actualizar, eliminar) de productos
 */

// Variables globales
let currentListId = null;

/**
 * Inicializar el gestor de listas
 * @param {number} listId - ID de la lista actual
 */
function initializeShoppingListManager(listId) {
    currentListId = listId;
    // NOTA: updateProgress() está comentada para evitar conflictos con shopping-list-detail.js
    // La función updateDetailProgress() en shopping-list-detail.js se encarga del progreso
}

// =============================================================================
// GESTIÓN DE MODALES
// =============================================================================

/**
 * Abrir modal para añadir producto
 */
function openAddProductModal() {
    document.getElementById('addProductModal').classList.remove('hidden');
}

/**
 * Cerrar modal para añadir producto
 */
function closeAddProductModal() {
    document.getElementById('addProductModal').classList.add('hidden');
    document.getElementById('addProductForm').reset();
}

/**
 * Abrir modal para editar producto
 * @param {number} productId - ID del producto
 * @param {string} name - Nombre del producto
 * @param {string} quantity - Cantidad del producto
 * @param {string} category - Categoría del producto
 * @param {number} price - Precio del producto
 */
function openEditProductModal(productId, name, quantity, category, price) {
    document.getElementById('edit_product_id').value = productId;
    document.getElementById('edit_product_name').value = name;
    document.getElementById('edit_product_quantity').value = quantity || '';
    document.getElementById('edit_product_category').value = category || 'General';
    document.getElementById('edit_product_price').value = price || '';
    document.getElementById('editProductModal').classList.remove('hidden');
}

/**
 * Cerrar modal para editar producto
 */
function closeEditProductModal() {
    document.getElementById('editProductModal').classList.add('hidden');
    document.getElementById('editProductForm').reset();
}

// =============================================================================
// OPERACIONES CRUD DE PRODUCTOS
// =============================================================================

/**
 * Añadir un nuevo producto a la lista
 * @param {Event} event - Evento del formulario
 */
async function addProduct(event) {
    event.preventDefault();

    // Recopilar datos del formulario
    const formData = {
        name: document.getElementById('add_product_name').value,
        quantity: document.getElementById('add_product_quantity').value,
        category: document.getElementById('add_product_category').value,
        price: document.getElementById('add_product_price').value || null
    };

    try {
        // Enviar petición al servidor
        const response = await fetch(`/listas/${currentListId}/productos`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            // Añadir producto al DOM
            addProductToDOM(result.product);
            closeAddProductModal();

            // Usar notificación desde shopping-list-detail.js si existe
            if (typeof showDetailNotification === 'function') {
                showDetailNotification('Producto añadido correctamente', 'success');
            } else {
                console.log('Producto añadido correctamente');
            }

            // Actualizar progreso si la función está disponible
            if (typeof updateDetailProgress === 'function') {
                updateDetailProgress();
            }

            // Recargar página para actualizar totales
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof showDetailNotification === 'function') {
                showDetailNotification(result.message || 'Error al añadir el producto', 'error');
            } else {
                console.error(result.message || 'Error al añadir el producto');
            }
        }
    } catch (error) {
        console.error('Error añadiendo producto:', error);
        if (typeof showDetailNotification === 'function') {
            showDetailNotification('Error de conexión', 'error');
        }
    }
}

/**
 * Actualizar un producto existente
 * @param {Event} event - Evento del formulario
 */
async function updateProduct(event) {
    event.preventDefault();

    const productId = document.getElementById('edit_product_id').value;

    // Recopilar datos del formulario
    const formData = {
        name: document.getElementById('edit_product_name').value,
        quantity: document.getElementById('edit_product_quantity').value,
        category: document.getElementById('edit_product_category').value,
        price: document.getElementById('edit_product_price').value || null
    };

    try {
        // Enviar petición al servidor
        const response = await fetch(`/listas/${currentListId}/productos/${productId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            // Actualizar producto en el DOM
            updateProductInDOM(productId, result.product);
            closeEditProductModal();

            // Usar notificación desde shopping-list-detail.js si existe
            if (typeof showDetailNotification === 'function') {
                showDetailNotification('Producto actualizado correctamente', 'success');
            } else {
                console.log('Producto actualizado correctamente');
            }

            // Recargar página para actualizar totales
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof showDetailNotification === 'function') {
                showDetailNotification(result.message || 'Error al actualizar el producto', 'error');
            } else {
                console.error(result.message || 'Error al actualizar el producto');
            }
        }
    } catch (error) {
        console.error('Error actualizando producto:', error);
        if (typeof showDetailNotification === 'function') {
            showDetailNotification('Error de conexión', 'error');
        }
    }
}

/**
 * Eliminar un producto de la lista
 * @param {number} productId - ID del producto a eliminar
 */
async function deleteProduct(productId) {
    // Confirmar antes de eliminar
    if (!confirm('¿Estás seguro de que quieres eliminar este producto?')) {
        return;
    }

    try {
        // Enviar petición al servidor
        const response = await fetch(`/listas/${currentListId}/productos/${productId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        });

        const result = await response.json();

        if (result.success) {
            // Eliminar producto del DOM
            const productElement = document.querySelector(`[data-product-id="${productId}"]`);
            if (productElement) {
                productElement.remove();
            }

            // Usar notificación desde shopping-list-detail.js si existe
            if (typeof showDetailNotification === 'function') {
                showDetailNotification('Producto eliminado correctamente', 'success');
            } else {
                console.log('Producto eliminado correctamente');
            }

            // Actualizar progreso si las funciones están disponibles
            if (typeof updateDetailProgress === 'function') {
                updateDetailProgress();
            }

            // Recargar página para actualizar totales
            setTimeout(() => location.reload(), 1000);
        } else {
            if (typeof showDetailNotification === 'function') {
                showDetailNotification(result.message || 'Error al eliminar el producto', 'error');
            } else {
                console.error(result.message || 'Error al eliminar el producto');
            }
        }
    } catch (error) {
        console.error('Error eliminando producto:', error);
        if (typeof showDetailNotification === 'function') {
            showDetailNotification('Error de conexión', 'error');
        }
    }
}

// =============================================================================
// ACTUALIZACIÓN DEL DOM
// =============================================================================

/**
 * Añadir un producto al DOM dinámicamente
 * @param {Object} product - Datos del producto
 */
function addProductToDOM(product) {
    const container = document.getElementById('products-container');

    if (!container) {
        console.warn('No se encontró el contenedor de productos');
        return;
    }

    // Determinar el color de la categoría
    const categoryColors = {
        'Frutas': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'Verduras': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        'Carnes': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
        'Pescados': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'Lácteos': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'Panadería': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'Bebidas': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'Limpieza': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'Higiene': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'Congelados': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'Otros': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
        'General': 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'
    };

    const colorClass = categoryColors[product.category] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';

    // Crear HTML del producto como fila de tabla
    const productHtml = `
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors product-row" data-product-id="${product.id}" data-category="${product.category || ''}">
            <td class="py-1.5 px-3">
                <input type="checkbox" class="h-3.5 w-3.5 text-green-600 rounded border-gray-300 dark:border-gray-600 focus:ring-green-500" onchange="toggleProduct(this)">
            </td>
            <td class="py-1.5 px-3">
                <div>
                    <h3 class="font-medium text-gray-900 dark:text-gray-100 product-name text-xs">${product.name || product.name_product}</h3>
                    ${product.category ? `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${colorClass}">${product.category}</span>` : ''}
                </div>
            </td>
            <td class="py-1.5 px-3">
                <span class="text-gray-900 dark:text-gray-100 product-quantity text-xs">${product.quantity || '1'}</span>
            </td>
            <td class="py-1.5 px-3">
                ${product.price && product.price > 0
                    ? `<span class="text-gray-900 dark:text-gray-100 font-medium product-price text-xs">${parseFloat(product.price).toFixed(2)}€</span>`
                    : `<span class="text-gray-400 dark:text-gray-500 text-xs">-</span>`
                }
            </td>
            <td class="py-1.5 px-3 no-print">
                <div class="flex items-center gap-1">
                    <button onclick="openEditProductModal(${product.id}, '${(product.name || product.name_product).replace(/'/g, "\\'")}', '${product.quantity || '1'}', '${product.category || ''}', ${product.price || 0})"
                            class="p-0.5 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-300 rounded transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </button>
                    <button onclick="deleteProduct(${product.id})"
                            class="p-0.5 text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 rounded transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </td>
        </tr>
    `;

    // Añadir al contenedor
    container.insertAdjacentHTML('beforeend', productHtml);
}

/**
 * Actualizar un producto existente en el DOM
 * @param {number} productId - ID del producto
 * @param {Object} product - Nuevos datos del producto
 */
function updateProductInDOM(productId, product) {
    const productElement = document.querySelector(`[data-product-id="${productId}"]`);

    if (!productElement) {
        console.warn(`No se encontró el elemento del producto con ID ${productId}`);
        return;
    }

    // Actualizar nombre del producto
    const nameElement = productElement.querySelector('.product-name');
    if (nameElement) {
        nameElement.textContent = product.name || product.name_product;
    } else {
        console.warn('No se encontró el elemento .product-name');
    }

    // Actualizar categoría (buscar tanto span como elemento con texto)
    const categoryElement = productElement.querySelector('span[class*="bg-"]');
    if (categoryElement && product.category) {
        categoryElement.textContent = product.category;
    }

    // Actualizar cantidad
    const quantityElement = productElement.querySelector('.product-quantity');
    if (quantityElement) {
        quantityElement.textContent = product.quantity || '1';
    } else {
        console.warn('No se encontró el elemento .product-quantity');
    }

    // Actualizar precio
    const priceElement = productElement.querySelector('.product-price');
    if (priceElement) {
        if (product.price && product.price > 0) {
            priceElement.textContent = `${parseFloat(product.price).toFixed(2)}€`;
            priceElement.className = 'text-gray-900 dark:text-gray-100 font-medium product-price text-xs';
        } else {
            priceElement.textContent = '-';
            priceElement.className = 'text-gray-400 dark:text-gray-500 text-xs';
        }
    } else {
        console.warn('No se encontró el elemento .product-price');
    }

    // Actualizar el botón de editar con los nuevos valores
    const editButton = productElement.querySelector('button[onclick*="openEditProductModal"]');
    if (editButton) {
        const newOnclick = `openEditProductModal(${productId}, '${(product.name || product.name_product).replace(/'/g, "\\'")}', '${product.quantity || '1'}', '${product.category || ''}', ${product.price || 0})`;
        editButton.setAttribute('onclick', newOnclick);
    }
}

// =============================================================================
// FUNCIONES DE UTILIDAD
// =============================================================================

/**
 * Alternar estado de producto (completado/pendiente)
 * @param {HTMLElement} checkbox - Elemento checkbox
 *
 * NOTA: Esta función está comentada para evitar conflictos con shopping-list-detail.js
 * que maneja específicamente la vista de detalles de listas
 */
/*
function toggleProduct(checkbox) {
    const productRow = checkbox.closest('div[class*="border"]');

    if (checkbox.checked) {
        // Marcar como completado
        productRow.classList.add('bg-green-50', 'opacity-60');
        productRow.style.textDecoration = 'line-through';
    } else {
        // Marcar como pendiente
        productRow.classList.remove('bg-green-50', 'opacity-60');
        productRow.style.textDecoration = 'none';
    }

    updateProgress();
}
*/

/**
 * Actualizar barra de progreso de la lista
 *
 * NOTA: Esta función está comentada para evitar conflictos con shopping-list-detail.js
 * que maneja específicamente la vista de detalles de listas
 */
/*
function updateProgress() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');
    const total = checkboxes.length;
    const completed = checkedBoxes.length;

    // Calcular porcentaje
    const percentage = total > 0 ? (completed / total) * 100 : 0;

    // Actualizar elementos UI
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');

    if (progressBar) {
        progressBar.style.width = percentage + '%';
    }

    if (progressText) {
        progressText.textContent = `${completed} de ${total} productos`;
    }
}
*/

/**
 * Mostrar notificación temporal
 *
 * NOTA: Esta función está comentada para evitar conflictos con shopping-list-detail.js
 * que tiene una implementación más avanzada de notificaciones
 */
/*
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md text-white ${
        type === 'success' ? 'bg-green-600' : 'bg-red-600'
    }`;
    notification.textContent = message;

    // Añadir al DOM
    document.body.appendChild(notification);

    // Eliminar después de 3 segundos
    setTimeout(() => {
        notification.remove();
    }, 3000);
}
*/
