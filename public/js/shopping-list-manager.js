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

    // Crear HTML del producto
    const productHtml = `
        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50" data-product-id="${product.id}">
            <div class="flex items-center gap-4">
                <input type="checkbox" class="h-5 w-5 text-green-600 rounded" onchange="toggleProduct(this)">
                <div>
                    <h3 class="font-medium text-gray-800 product-name">${product.name}</h3>
                    <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full mt-1 product-category">
                        ${product.category}
                    </span>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    ${product.quantity && product.quantity !== '1' ? `<p class="font-semibold text-gray-800 product-quantity">${product.quantity}</p>` : ''}
                    ${product.price && product.price > 0 ? `<p class="text-sm text-gray-600 product-price">${parseFloat(product.price).toFixed(2)}€</p>` : ''}
                </div>
                <div class="flex gap-2 no-print">
                    <button onclick="openEditProductModal(${product.id}, '${product.name}', '${product.quantity}', '${product.category}', ${product.price})"
                            class="text-blue-600 hover:text-blue-800 text-sm" title="Editar producto">
                        ✏️
                    </button>
                    <button onclick="deleteProduct(${product.id})"
                            class="text-red-600 hover:text-red-800 text-sm" title="Eliminar producto">
                        🗑️
                    </button>
                </div>
            </div>
        </div>
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

    if (!productElement) return;

    // Actualizar nombre y categoría
    productElement.querySelector('.product-name').textContent = product.name;
    productElement.querySelector('.product-category').textContent = product.category;

    // Actualizar cantidad
    const quantityElement = productElement.querySelector('.product-quantity');
    if (product.quantity && product.quantity !== '1') {
        if (quantityElement) {
            quantityElement.textContent = product.quantity;
        } else {
            // Crear elemento de cantidad si no existe
            const priceContainer = productElement.querySelector('.text-right');
            priceContainer.insertAdjacentHTML('afterbegin',
                `<p class="font-semibold text-gray-800 product-quantity">${product.quantity}</p>`);
        }
    } else if (quantityElement) {
        quantityElement.remove();
    }

    // Actualizar precio
    const priceElement = productElement.querySelector('.product-price');
    if (product.price && product.price > 0) {
        if (priceElement) {
            priceElement.textContent = `${parseFloat(product.price).toFixed(2)}€`;
        } else {
            // Crear elemento de precio si no existe
            const priceContainer = productElement.querySelector('.text-right');
            priceContainer.insertAdjacentHTML('beforeend',
                `<p class="text-sm text-gray-600 product-price">${parseFloat(product.price).toFixed(2)}€</p>`);
        }
    } else if (priceElement) {
        priceElement.remove();
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
