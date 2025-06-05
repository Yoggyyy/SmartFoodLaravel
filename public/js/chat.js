/**
 * SmartFood - Chat con IA (Enfoque Híbrido)
 *
 * Este archivo maneja el chat inteligente con persistencia de conversaciones por usuario:
 * - Backend para gestión de conversaciones y mensajes por usuario
 * - LocalStorage como cache y fallback para mejor UX
 * - Sincronización automática entre cliente y servidor
 */

// Variables globales del chat
let conversations = {};
let currentConversationId = null;
let conversationCounter = 0;
let messageCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Limpiar localStorage obsoleto de conversaciones antiguas
    localStorage.removeItem('smartfood_conversations');

    // Cargar conversaciones desde el servidor (usuario actual)
    loadConversationsFromServer();

    // Configurar eventos del chat
    document.getElementById('new-list-btn').addEventListener('click', createNewConversation);
    document.getElementById('message-form').addEventListener('submit', sendMessage);

    // Configurar textarea auto-expandible
    const messageInput = document.getElementById('message-input');

    // Auto-expandir textarea
    messageInput.addEventListener('input', function() {
        autoResizeTextarea(this);
    });

    // Manejar Enter (enviar) y Shift+Enter (nueva línea)
    messageInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(e);
        }
    });

    // Obtener información del usuario para personalizar el chat
    fetchUserProfile();
});

/**
 * Auto-redimensionar textarea según el contenido
 * @param {HTMLTextAreaElement} textarea - Elemento textarea
 */
function autoResizeTextarea(textarea) {
    // Resetear altura para obtener la altura real del contenido
    textarea.style.height = 'auto';

    // Calcular nueva altura basada en scrollHeight
    const newHeight = Math.min(textarea.scrollHeight, 128); // max-h-32 = 128px

    // Aplicar nueva altura
    textarea.style.height = newHeight + 'px';
}

/**
 * Cargar conversaciones del usuario desde el servidor
 */
async function loadConversationsFromServer() {
    try {
        console.log('Cargando conversaciones del servidor...');

        const result = await apiRequest('/chat/conversations');
        console.log('Respuesta del servidor:', result);

        if (result.success) {
            conversations = {};

            // Procesar conversaciones del servidor
            result.data.forEach(conv => {
                conversations[conv.id] = {
                    id: conv.id,
                    name: conv.name,
                    messages: conv.messages || [],
                    created_at: conv.created_at,
                    updated_at: conv.updated_at
                };

                // Agregar al sidebar
                addConversationToSidebar(conv.id, conv.name);
            });

            // Si no hay conversaciones, crear la primera
            if (result.data.length === 0) {
                console.log('No hay conversaciones, creando la primera...');
                await createNewConversation();
            } else {
                // Seleccionar la conversación más reciente
                const latestConversation = result.data[0];
                console.log('Seleccionando conversación más reciente:', latestConversation.id);
                selectConversation(latestConversation.id);
            }

            console.log('Conversaciones cargadas exitosamente:', Object.keys(conversations).length);

            // Guardar en cache local
            saveConversationsToCache();

        } else {
            console.error('Error del servidor al cargar conversaciones:', result);
            showMessage(`Error del servidor: ${result.message || 'Error desconocido'}`, 'error');

            // Fallback: cargar desde localStorage si falla el servidor
            console.log('Intentando cargar desde cache local...');
            loadConversationsFromCache();

            if (Object.keys(conversations).length === 0) {
                console.log('No hay conversaciones en cache, creando nueva...');
                await createNewConversation();
            }
        }

    } catch (error) {
        console.error('Error de conexión al cargar conversaciones:', error);
        showMessage(`Error de conexión: ${error.message}`, 'error');

        // Fallback: cargar desde localStorage
        console.log('Intentando cargar desde cache local como fallback...');
        loadConversationsFromCache();

        if (Object.keys(conversations).length === 0) {
            console.log('No hay conversaciones en cache, creando nueva offline...');
            await createNewConversation();
        }
    }
}

/**
 * Crear nueva conversación (híbrido: servidor + cache)
 */
async function createNewConversation() {
    try {
        console.log('Creando nueva conversación...');

        const result = await apiRequest('/chat/conversations', {
            method: 'POST',
            body: JSON.stringify({
                name: 'Nueva Lista'
            })
        });

        if (result.success) {
            const conversationId = result.data.id;
            const conversationName = result.data.name;

            // Crear objeto de conversación local
            conversations[conversationId] = {
                id: conversationId,
                name: conversationName,
                messages: [],
                created_at: result.data.created_at,
                updated_at: result.data.updated_at
            };

            // Agregar al sidebar y seleccionar
            addConversationToSidebar(conversationId, conversationName);
            selectConversation(conversationId);

            // Guardar en cache
            saveConversationsToCache();

            console.log('Conversación creada exitosamente:', conversationId);

        } else {
            console.error('Error al crear conversación:', result.message);
            showMessage('Error al crear nueva conversación', 'error');
        }

    } catch (error) {
        console.error('Error de conexión al crear conversación:', error);

        // Fallback: crear conversación solo localmente
        conversationCounter++;
        const conversationId = `local_${conversationCounter}`;
        const conversationName = `Nueva Lista`;

        conversations[conversationId] = {
            id: conversationId,
            name: conversationName,
            messages: [],
            created_at: new Date().toISOString(),
            isLocal: true // Marcar como local para sincronizar después
        };

        addConversationToSidebar(conversationId, conversationName);
        selectConversation(conversationId);
        saveConversationsToCache();

        showMessage('Conversación creada offline', 'warning');
    }
}

/**
 * Agregar conversación al sidebar
 * @param {string} conversationId - ID único de la conversación
 * @param {string} conversationName - Nombre visible de la conversación
 */
function addConversationToSidebar(conversationId, conversationName) {
    const conversationHtml = `
        <div class="conversation-item flex items-center justify-between p-3 hover:bg-green-50 cursor-pointer rounded-lg transition-colors group"
             data-conversation-id="${conversationId}" onclick="selectConversation('${conversationId}')">
            <div class="flex items-center gap-3 flex-1">
                <div class="w-2 h-2 bg-green-600 rounded-full"></div>
                <span class="text-sm text-gray-600 truncate">${conversationName}</span>
            </div>
            <button onclick="deleteConversation('${conversationId}', event)"
                    class="delete-btn opacity-0 group-hover:opacity-100 text-red-600 hover:text-red-600 text-xs transition-opacity"
                    title="Eliminar conversación">
                ✕
            </button>
        </div>
    `;

    document.getElementById('conversations-list').insertAdjacentHTML('beforeend', conversationHtml);
}

/**
 * Seleccionar y mostrar una conversación específica
 * @param {string} conversationId - ID de la conversación a seleccionar
 */
function selectConversation(conversationId) {
    if (!conversations[conversationId]) {
        console.error('Conversación no encontrada:', conversationId);
        return;
    }

    // Quitar selección de la conversación anterior
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('bg-green-100', 'border-l-4', 'border-green-600');
    });

    // Marcar la nueva conversación como seleccionada
    const selectedItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
    if (selectedItem) {
        selectedItem.classList.add('bg-green-100', 'border-l-4', 'border-green-600');
    }

    // Limpiar y cargar mensajes de la conversación
    const chatContainer = document.getElementById('chat-container');
    chatContainer.innerHTML = '';

    if (conversations[conversationId] && conversations[conversationId].messages) {
        conversations[conversationId].messages.forEach(message => {
            if (message.type === 'user') {
                addUserMessageToDOM(message.content, message.id);
            } else if (message.type === 'bot') {
                addBotMessageToDOM(message.content, message.id);
            }
        });
        scrollToBottom();
    }

    // Guardar en cache
    saveConversationsToCache();

    console.log('Conversación seleccionada:', conversationId, 'con', conversations[conversationId]?.messages?.length || 0, 'mensajes');
}

/**
 * Actualizar el nombre de una conversación en el sidebar
 * @param {string} conversationId - ID de la conversación
 * @param {string} newName - Nuevo nombre para la conversación
 */
function updateConversationNameInSidebar(conversationId, newName) {
    const conversationElement = document.querySelector(`[data-conversation-id="${conversationId}"] span`);
    if (conversationElement) {
        conversationElement.textContent = newName;
    }
}

/**
 * Eliminar conversación (híbrido: servidor + cache)
 * @param {string} conversationId - ID de la conversación a eliminar
 * @param {Event} event - Evento del click para prevenir propagación
 */
async function deleteConversation(conversationId, event) {
    event.stopPropagation();

    // No permitir eliminar si es la única conversación
    if (Object.keys(conversations).length <= 1) {
        showMessage('No puedes eliminar la última conversación', 'error');
        return;
    }

    try {
        // Si es una conversación del servidor, eliminarla del backend
        if (!conversations[conversationId].isLocal) {
            const result = await apiRequest(`/chat/conversations/${conversationId}`, {
                method: 'DELETE'
            });

            if (!result.success) {
                showMessage('Error al eliminar conversación del servidor', 'error');
                return;
            }
        }

        // Eliminar localmente
        delete conversations[conversationId];
        document.querySelector(`[data-conversation-id="${conversationId}"]`).remove();

        // Si era la conversación activa, seleccionar otra
        if (currentConversationId === conversationId) {
            const firstConversationId = Object.keys(conversations)[0];
            selectConversation(firstConversationId);
        }

        // Guardar en cache
        saveConversationsToCache();
        showMessage('Conversación eliminada', 'success');

    } catch (error) {
        console.error('Error al eliminar conversación:', error);
        showMessage('Error al eliminar conversación', 'error');
    }
}

/**
 * Enviar mensaje desde el formulario
 * @param {Event} e - Evento del formulario
 */
async function sendMessage(e) {
    e.preventDefault();

    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();

    if (!message || !currentConversationId) return;

    // Limpiar input y agregar mensaje del usuario
    messageInput.value = '';
    await addUserMessage(message);

    // Mostrar indicador de "escribiendo..."
    showTypingIndicator();

    // Enviar a la API de chat
    await sendMessageToAPI(message, currentConversationId);
}

/**
 * Enviar mensaje a la API de OpenAI
 * @param {string} message - Mensaje del usuario
 * @param {string} conversationId - ID de la conversación actual
 */
async function sendMessageToAPI(message, conversationId) {
    try {
        const result = await apiRequest('/chat/send-message', {
            method: 'POST',
            body: JSON.stringify({
                message: message,
                conversation_id: conversationId
            })
        });

        // Ocultar indicador de "escribiendo..."
        hideTypingIndicator();

        if (result.success) {
            // Agregar respuesta del bot
            await addBotMessage(result.data.bot_message);

            // Log para debugging (opcional)
            console.log('Tokens usados:', result.data.tokens_used);
            console.log('Respuesta desde caché:', result.data.cached);
        } else {
            // Mostrar error
            await addBotMessage('Lo siento, hubo un error al procesar tu mensaje. Por favor, inténtalo de nuevo.');
            console.error('Error de API:', result.message);
        }

    } catch (error) {
        hideTypingIndicator();
        await addBotMessage('Error de conexión. Por favor, verifica tu conexión a internet e inténtalo de nuevo.');
        console.error('Error de red:', error);
    }
}

/**
 * Mostrar indicador de "escribiendo..." del bot
 */
function showTypingIndicator() {
    const typingHtml = `
        <div class="flex items-start gap-3" id="typing-indicator">
            <img src="${window.location.origin}/images/logoManzana.webp" alt="Bot" class="h-8 w-8 rounded-full" />
            <div class="bg-white rounded-lg p-4 shadow text-gray-900 max-w-xl">
                <div class="flex items-center gap-1">
                    <span class="text-sm text-gray-600">SmartFood está escribiendo</span>
                    <div class="flex gap-1 ml-2">
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.getElementById('chat-container').insertAdjacentHTML('beforeend', typingHtml);
    scrollToBottom();
}

/**
 * Ocultar indicador de "escribiendo..."
 */
function hideTypingIndicator() {
    const typingIndicator = document.getElementById('typing-indicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

/**
 * Agregar mensaje del usuario (híbrido: local + servidor)
 * @param {string} message - Texto del mensaje
 */
async function addUserMessage(message) {
    if (!currentConversationId) return;

    messageCounter++;
    const messageId = `user-msg-${messageCounter}`;

    // Crear objeto de mensaje local
    const messageData = {
        id: messageId,
        content: message,
        type: 'user',
        timestamp: new Date().toISOString()
    };

    // Agregar a conversación local
    conversations[currentConversationId].messages.push(messageData);

    // Agregar al DOM inmediatamente (UX)
    addUserMessageToDOM(message, messageId);
    scrollToBottom();

    // Sincronizar con servidor en background
    syncMessageToServer(currentConversationId, message, 'user');

    // Guardar en cache
    saveConversationsToCache();
}

/**
 * Agregar mensaje del bot (híbrido: local + servidor)
 * @param {string} message - Texto del mensaje
 */
async function addBotMessage(message) {
    if (!currentConversationId) return;

    messageCounter++;
    const messageId = `bot-msg-${messageCounter}`;

    // Crear objeto de mensaje local
    const messageData = {
        id: messageId,
        content: message,
        type: 'bot',
        timestamp: new Date().toISOString()
    };

    // Agregar a conversación local
    conversations[currentConversationId].messages.push(messageData);

    // Agregar al DOM inmediatamente (UX)
    addBotMessageToDOM(message, messageId);
    scrollToBottom();

    // Detectar y crear lista de compra si aplica
    await detectAndCreateShoppingList(message, messageId);

    // Intentar actualizar el nombre de la conversación basado en el contenido
    await tryUpdateConversationName(message);

    // Sincronizar con servidor en background
    syncMessageToServer(currentConversationId, message, 'bot');

    // Guardar en cache
    saveConversationsToCache();

    return messageId;
}

/**
 * Intentar actualizar el nombre de la conversación basado en el contenido
 * @param {string} message - Mensaje del bot para analizar
 */
async function tryUpdateConversationName(message) {
    if (!currentConversationId || !conversations[currentConversationId]) return;

    const currentName = conversations[currentConversationId].name;

    // Detectar supermercado mencionado en el mensaje
    const supermarkets = ['mercadona', 'carrefour', 'dia', 'lidl', 'aldi', 'eroski', 'hipercor', 'alcampo'];
    const lowerMessage = message.toLowerCase();

    const mentionedSupermarket = supermarkets.find(market => lowerMessage.includes(market));

    let newName = null;

    // Determinar nuevo nombre basado en el contenido
    if (mentionedSupermarket) {
        newName = `Lista de ${mentionedSupermarket.charAt(0).toUpperCase() + mentionedSupermarket.slice(1)}`;
    } else if (lowerMessage.includes('lista de compra') || lowerMessage.includes('lista para comprar')) {
        // Si menciona "lista de compra" pero no hay supermercado específico
        if (currentName === 'Nueva Lista') {
            newName = 'Lista General';
        }
    }

    // También revisar mensajes del usuario para detectar supermercado
    if (!newName && currentName === 'Nueva Lista') {
        const userMessages = conversations[currentConversationId].messages.filter(msg => msg.type === 'user');
        for (let i = userMessages.length - 1; i >= 0; i--) {
            const userMessage = userMessages[i].content.toLowerCase();
            const userSupermarketMatch = supermarkets.find(s => userMessage.includes(s));
            if (userSupermarketMatch) {
                newName = `Lista de ${userSupermarketMatch.charAt(0).toUpperCase() + userSupermarketMatch.slice(1)}`;
                break;
            }
        }
    }

    // Solo actualizar si tenemos un nuevo nombre y es diferente al actual
    if (newName && newName !== currentName) {
        console.log(`Actualizando nombre de conversación: "${currentName}" → "${newName}"`);

        // Actualizar localmente inmediatamente para mejor UX
        conversations[currentConversationId].name = newName;
        updateConversationNameInSidebar(currentConversationId, newName);
        saveConversationsToCache();

        // Sincronizar con servidor si no es local
        if (!conversations[currentConversationId].isLocal) {
            try {
                const result = await apiRequest(`/chat/conversations/${currentConversationId}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        name: newName
                    })
                });

                if (result.success) {
                    console.log('Nombre actualizado en servidor exitosamente');
                    // Actualizar datos locales con respuesta del servidor
                    conversations[currentConversationId].name = result.data.name;
                    conversations[currentConversationId].updated_at = result.data.updated_at;
                    updateConversationNameInSidebar(currentConversationId, result.data.name);
                    saveConversationsToCache();
                } else {
                    console.error('Error actualizando nombre en servidor:', result.message);
                    // Revertir cambio local si falla el servidor
                    conversations[currentConversationId].name = currentName;
                    updateConversationNameInSidebar(currentConversationId, currentName);
                    saveConversationsToCache();
                }
            } catch (error) {
                console.error('Error actualizando nombre en servidor:', error);
                // Revertir cambio local si falla la conexión
                conversations[currentConversationId].name = currentName;
                updateConversationNameInSidebar(currentConversationId, currentName);
                saveConversationsToCache();
            }
        }
    }
}

/**
 * Detectar si el mensaje del bot contiene una lista de compra y crearla
 * @param {string} message - Mensaje del bot
 * @param {string} messageId - ID del mensaje
 */
async function detectAndCreateShoppingList(message, messageId) {
    try {
        // Palabras clave que indican una lista de compra
        const shoppingListKeywords = [
            'lista de compra', 'lista de la compra', 'lista para comprar',
            'necesitas comprar', 'productos:', 'ingredientes:',
            'presupuesto', 'supermercado', 'comprar:'
        ];

        const lowerMessage = message.toLowerCase();
        const containsShoppingList = shoppingListKeywords.some(keyword =>
            lowerMessage.includes(keyword)
        );

        if (!containsShoppingList) {
            console.log('Mensaje no contiene palabras clave de lista de compra');
            return;
        }

        console.log('Detectada posible lista de compra en mensaje');

        // Extraer información de la lista del mensaje del bot
        const listData = parseShoppingListFromMessage(message);

        if (!listData || !listData.products || listData.products.length === 0) {
            console.log('No se pudieron extraer productos válidos del mensaje');
            return;
        }

        console.log('Datos de lista extraídos:', listData);

        // Extraer presupuesto del mensaje del usuario (último mensaje del usuario en la conversación)
        const userBudget = extractBudgetFromUserMessage();
        console.log('Presupuesto extraído del usuario:', userBudget);

        if (userBudget) {
            listData.budget = userBudget;
            console.log('Presupuesto asignado a la lista:', listData.budget);
        }

        // Crear lista en la base de datos
        console.log('Enviando datos de lista al servidor:', listData);
        const result = await createShoppingListFromParsedData(listData);

        if (result.success) {
            // Agregar botón para ver la lista creada
            addViewListButton(messageId, result.data.id);
            showMessage('Lista de compra creada y guardada exitosamente', 'success');
            console.log('Lista creada con ID:', result.data.id);
        } else {
            console.error('Error al crear lista:', result.error || result.message);
            showMessage(`Error al crear lista: ${result.error || result.message}`, 'error');
        }

    } catch (error) {
        console.error('Error al detectar/crear lista de compra:', error);
        showMessage('Error inesperado al crear la lista de compra', 'error');
    }
}

/**
 * Extraer presupuesto del mensaje del usuario
 * @returns {number|null} Presupuesto extraído o null
 */
function extractBudgetFromUserMessage() {
    if (!currentConversationId || !conversations[currentConversationId]) {
        console.log('No hay conversación actual para extraer presupuesto');
        return null;
    }

    const conversation = conversations[currentConversationId];

    // Buscar en los mensajes del usuario de la conversación actual
    const userMessages = conversation.messages.filter(msg => msg.type === 'user');
    console.log('Mensajes de usuario encontrados:', userMessages.length);

    // Buscar presupuesto en todos los mensajes del usuario (del más reciente al más antiguo)
    for (let i = userMessages.length - 1; i >= 0; i--) {
        const userMessage = userMessages[i].content;
        console.log('Buscando presupuesto en mensaje:', userMessage);

        // Patrones múltiples para detectar presupuesto
        const budgetPatterns = [
            /presupuesto[:\s]*de[:\s]*([0-9]+(?:[.,][0-9]+)?)\s*€?/i,
            /presupuesto[:\s]*([0-9]+(?:[.,][0-9]+)?)\s*€?/i,
            /con[:\s]*([0-9]+(?:[.,][0-9]+)?)\s*€[:\s]*de[:\s]*presupuesto/i,
            /([0-9]+(?:[.,][0-9]+)?)\s*€[:\s]*de[:\s]*presupuesto/i,
            /un[:\s]*presupuesto[:\s]*de[:\s]*([0-9]+(?:[.,][0-9]+)?)\s*€?/i,
            /máximo[:\s]*([0-9]+(?:[.,][0-9]+)?)\s*€?/i,
            /hasta[:\s]*([0-9]+(?:[.,][0-9]+)?)\s*€?/i
        ];

        for (const pattern of budgetPatterns) {
            const budgetMatch = userMessage.match(pattern);
            if (budgetMatch) {
                const budget = parseFloat(budgetMatch[1].replace(',', '.'));
                console.log('Presupuesto encontrado:', budget);
                return budget;
            }
        }
    }

    console.log('No se encontró presupuesto en mensajes del usuario');
    return null;
}

/**
 * Parsear mensaje del bot para extraer datos de la lista de compra
 * @param {string} message - Mensaje del bot
 * @returns {Object|null} Datos parseados de la lista
 */
function parseShoppingListFromMessage(message) {
    const listData = {
        name_list: '',
        conversation_id: null,
        conversation_title: 'Lista General',
        budget: null,
        supermarket_name: null,
        products: []
    };

    // Verificar que tenemos una conversación válida
    if (!currentConversationId || !conversations[currentConversationId]) {
        console.error('No hay conversación activa para crear lista');
        return null;
    }

    // Solo usar conversation_id si es numérico (del servidor), no si es local
    if (conversations[currentConversationId].isLocal) {
        console.log('Conversación es local, se creará nueva en el servidor');
        listData.conversation_id = null; // Forzar creación de nueva conversación
    } else {
        // Asegurar que el conversation_id es una cadena válida
        listData.conversation_id = String(currentConversationId);
    }

    console.log('Parseando lista para conversación:', currentConversationId, 'isLocal:', conversations[currentConversationId].isLocal);

    // Extraer supermercado
    const supermarkets = ['mercadona', 'carrefour', 'lidl', 'alcampo', 'dia', 'eroski', 'auchan', 'consum', 'hipercor'];
    const supermarketMatch = supermarkets.find(s =>
        message.toLowerCase().includes(s)
    );
    console.log('Supermercado encontrado en mensaje del bot:', supermarketMatch);

    // También verificar mensajes del usuario para detectar supermercado específico
    let userSpecifiedSupermarket = null;
    if (currentConversationId && conversations[currentConversationId]) {
        const userMessages = conversations[currentConversationId].messages.filter(msg => msg.type === 'user');
        console.log('Revisando mensajes del usuario para supermercado:', userMessages.length);
        for (let i = userMessages.length - 1; i >= 0; i--) {
            const userMessage = userMessages[i].content.toLowerCase();
            console.log('Mensaje del usuario:', userMessage);
            const userSupermarketMatch = supermarkets.find(s => userMessage.includes(s));
            if (userSupermarketMatch) {
                userSpecifiedSupermarket = userSupermarketMatch;
                console.log('Supermercado encontrado en mensaje del usuario:', userSpecifiedSupermarket);
                break;
            }
        }
    }

    if (supermarketMatch) {
        listData.supermarket_name = supermarketMatch.charAt(0).toUpperCase() + supermarketMatch.slice(1);
    } else if (userSpecifiedSupermarket) {
        listData.supermarket_name = userSpecifiedSupermarket.charAt(0).toUpperCase() + userSpecifiedSupermarket.slice(1);
    }

    console.log('Supermercado final asignado:', listData.supermarket_name);

    // Generar nombre de la lista basado en el supermercado
    if (listData.supermarket_name) {
        listData.name_list = `Lista de ${listData.supermarket_name}`;
        listData.conversation_title = `Lista de ${listData.supermarket_name}`;
    } else {
        listData.name_list = 'Lista General';
        listData.conversation_title = 'Lista General';
    }

    // Validar que el nombre no exceda 255 caracteres
    if (listData.name_list.length > 255) {
        listData.name_list = listData.name_list.substring(0, 255);
    }
    if (listData.conversation_title.length > 255) {
        listData.conversation_title = listData.conversation_title.substring(0, 255);
    }

    // Actualizar el nombre de la conversación actual si es diferente
    if (currentConversationId && conversations[currentConversationId]) {
        const currentName = conversations[currentConversationId].name;
        console.log('Nombre actual de conversación:', currentName);
        console.log('Nuevo nombre propuesto:', listData.conversation_title);

        // Actualizar si es 'Nueva Lista' o si ya tiene un nombre de lista pero es diferente
        if ((currentName === 'Nueva Lista' || currentName.includes('Lista de compra ')) && currentName !== listData.conversation_title) {
            conversations[currentConversationId].name = listData.conversation_title;
            console.log('Actualizando nombre de conversación a:', listData.conversation_title);

            // Actualizar el nombre en el sidebar
            updateConversationNameInSidebar(currentConversationId, listData.conversation_title);
            saveConversationsToCache();

            // Sincronizar con servidor si no es local
            if (!conversations[currentConversationId].isLocal) {
                // Hacer la actualización en background sin esperar
                apiRequest(`/chat/conversations/${currentConversationId}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        name: listData.conversation_title
                    })
                }).then(result => {
                    if (result.success) {
                        console.log('Nombre de conversación sincronizado con servidor');
                        conversations[currentConversationId].updated_at = result.data.updated_at;
                        saveConversationsToCache();
                    } else {
                        console.error('Error sincronizando nombre con servidor:', result.message);
                    }
                }).catch(error => {
                    console.error('Error de conexión al sincronizar nombre:', error);
                });
            }
        }
    }

    // Extraer productos (buscar listas numeradas o con viñetas)
    const productPatterns = [
        /(?:^|\n)\s*[-*•]\s*([^\n]+)/gm,  // Viñetas: - * •
        /(?:^|\n)\s*\d+[.)]\s*([^\n]+)/gm, // Numeradas: 1. 1)
        /(?:^|\n)\s*([A-ZÁÉÍÓÚÑ][^:\n]*?)(?:\s*[-–—]\s*[^\n]+)?(?=\n|$)/gm // Líneas que empiecen con mayúscula
    ];

    for (const pattern of productPatterns) {
        const matches = [...message.matchAll(pattern)];
        if (matches.length >= 2) { // Al menos 2 productos para considerar una lista
            listData.products = matches.map(match => {
                const productText = match[1].trim();
                return parseProductFromText(productText);
            }).filter(p => p.name.length > 2 && p.name.length <= 255); // Validar longitud
            break;
        }
    }

    // Si no encontramos productos con patrones, buscar después de "productos:" o "ingredientes:"
    if (listData.products.length === 0) {
        const productSectionMatch = message.match(/(?:productos?|ingredientes?):\s*([^]+?)(?:\n\n|$)/i);
        if (productSectionMatch) {
            const productText = productSectionMatch[1];
            const lines = productText.split('\n').filter(line => line.trim());
            listData.products = lines.map(line => parseProductFromText(line.trim()))
                                   .filter(p => p.name.length > 2 && p.name.length <= 255);
        }
    }

    console.log('Productos extraídos:', listData.products.length);

    // Validar que tenemos datos mínimos válidos
    if (!listData.name_list || !listData.conversation_title) {
        console.error('Datos de lista inválidos: faltan campos requeridos');
        return null;
    }

    return listData.products.length > 0 ? listData : null;
}

/**
 * Parsear un producto individual desde texto
 * @param {string} text - Texto del producto
 * @returns {Object} Datos del producto
 */
function parseProductFromText(text) {
    // Limpiar el texto
    text = text.replace(/^[-*•\d+.)\s]+/, '').trim();

    // Extraer precio si está presente (formato: - 5.00€, - €5.00, etc.)
    let price = 0;
    const priceMatch = text.match(/[-–—]\s*([€]?(?:\d+(?:[.,]\d+)?)\s*[€]?)/);
    if (priceMatch) {
        const priceStr = priceMatch[1].replace(/[€\s]/g, '').replace(',', '.');
        price = parseFloat(priceStr) || 0;
        // Remover el precio del nombre
        text = text.replace(/\s*[-–—]\s*[€]?(?:\d+(?:[.,]\d+)?)\s*[€]?/, '').trim();
    }

    // Extraer cantidad si está presente
    const quantityMatch = text.match(/^(\d+(?:[.,]\d+)?\s*(?:kg|g|l|ml|unidades?|uds?|piezas?|u\.?))\s+(.+)/i);

    let quantity = '1';
    let name = text;

    if (quantityMatch) {
        quantity = quantityMatch[1];
        name = quantityMatch[2];
    } else {
        // Buscar cantidad al final
        const endQuantityMatch = text.match(/^(.+?)\s+(\d+(?:[.,]\d+)?\s*(?:kg|g|l|ml|unidades?|uds?|piezas?|u\.?))$/i);
        if (endQuantityMatch) {
            name = endQuantityMatch[1];
            quantity = endQuantityMatch[2];
        }
    }

    // También extraer cantidad y precio de formatos como "Carne de ternera (300g) - 5.00€"
    const complexMatch = text.match(/^(.+?)\s*\(([^)]+)\)\s*(.*)$/);
    if (complexMatch) {
        name = complexMatch[1].trim();
        quantity = complexMatch[2].trim();
        // El precio ya se extrajo arriba
    }

    // Limitar la longitud de la cantidad a 50 caracteres para evitar errores de base de datos
    if (quantity && quantity.length > 50) {
        quantity = quantity.substring(0, 50);
    }

    // Determinar categoría básica
    const categories = {
        'Lácteos': ['leche', 'yogur', 'queso', 'mantequilla', 'nata'],
        'Carnes': ['pollo', 'carne', 'ternera', 'cerdo', 'jamón'],
        'Frutas': ['manzana', 'plátano', 'naranja', 'pera', 'uva'],
        'Verduras': ['tomate', 'lechuga', 'cebolla', 'patata', 'zanahoria'],
        'Cereales': ['pan', 'arroz', 'pasta', 'cereales'],
        'Pescado': ['pescado', 'salmón', 'atún', 'merluza']
    };

    let category = 'General';
    const lowerName = name.toLowerCase();

    for (const [cat, keywords] of Object.entries(categories)) {
        if (keywords.some(keyword => lowerName.includes(keyword))) {
            category = cat;
            break;
        }
    }

    return {
        name: name.trim(),
        quantity: quantity,
        category: category,
        price: price
    };
}

/**
 * Crear lista de compra en la base de datos con datos parseados
 * @param {Object} listData - Datos de la lista parseados
 * @returns {Promise<Object>} Resultado de la creación
 */
async function createShoppingListFromParsedData(listData) {
    try {
        console.log('Enviando datos de lista al servidor:', listData);

        const response = await fetch('/listas/create-from-chat', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(listData)
        });

        const result = await response.json();
        console.log('Respuesta del servidor para crear lista:', result);

        if (!response.ok) {
            console.error('Error HTTP al crear lista:', response.status, result);

            // Manejar errores de validación específicos
            if (response.status === 422 && result.errors) {
                const errorMessages = Object.values(result.errors).flat().join(', ');
                return {
                    success: false,
                    error: `Error de validación: ${errorMessages}`
                };
            }

            return {
                success: false,
                error: `Error ${response.status}: ${result.message || 'Error del servidor'}`
            };
        }

        if (result.success) {
            console.log('Lista creada exitosamente:', result.data);
        } else {
            console.error('Error en la respuesta del servidor:', result);
        }

        return result;

    } catch (error) {
        console.error('Error de red al crear lista:', error);
        return {
            success: false,
            error: `Error de conexión: ${error.message}`
        };
    }
}

/**
 * Agregar botón para ver la lista creada junto al mensaje del bot
 * @param {string} messageId - ID del mensaje del bot
 * @param {number} listId - ID de la lista creada
 */
function addViewListButton(messageId, listId) {
    const messageElement = document.getElementById(messageId);
    if (messageElement) {
        const buttonHtml = `
            <div class="mt-3 pt-3 border-t border-gray-100">
                <a href="/listas/${listId}"
                   class="inline-flex items-center gap-2 bg-green-100 hover:bg-green-600 hover:text-white text-gray-900 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <span>📋</span> Ver Lista Creada
                </a>
            </div>
        `;
        messageElement.insertAdjacentHTML('beforeend', buttonHtml);
    }
}

/**
 * Crear elemento visual del mensaje del usuario
 * @param {string} message - Texto del mensaje
 * @param {string} messageId - ID único del mensaje
 */
function addUserMessageToDOM(message, messageId) {
    const messageHtml = `
        <div class="flex flex-col items-end gap-1">
            <div class="bg-green-600 text-white rounded-lg p-4 shadow max-w-xl ml-auto" id="${messageId}">
                ${message}
            </div>
            <button class="flex items-center gap-1 text-green-600 hover:text-green-700 text-xs mr-2" onclick="copyMessage('${messageId}')">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a1 1 0 011 1v3M9 7h4' />
                </svg>
                Copiar
            </button>
        </div>
    `;

    document.getElementById('chat-container').insertAdjacentHTML('beforeend', messageHtml);
    scrollToBottom();
}

/**
 * Crear elemento visual del mensaje del bot
 * @param {string} message - Texto del mensaje
 * @param {string} messageId - ID único del mensaje
 */
function addBotMessageToDOM(message, messageId) {
    const messageHtml = `
        <div class="flex flex-col items-start gap-1">
            <div class="flex items-start gap-3">
                <img src="${window.location.origin}/images/logoManzana.webp" alt="Bot" class="h-8 w-8 rounded-full" />
                <div class="bg-white rounded-lg p-4 shadow text-gray-900 max-w-xl" id="${messageId}">
                    ${message}
                </div>
            </div>
            <button class="flex items-center gap-1 text-green-600 hover:text-green-700 text-xs ml-11" onclick="copyMessage('${messageId}')">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a1 1 0 011 1v3M9 7h4' />
                </svg>
                Copiar
            </button>
        </div>
    `;

    document.getElementById('chat-container').insertAdjacentHTML('beforeend', messageHtml);
    scrollToBottom();
}

/**
 * Copiar mensaje al portapapeles
 * @param {string} messageId - ID del mensaje a copiar
 */
function copyMessage(messageId) {
    const messageElement = document.getElementById(messageId);
    navigator.clipboard.writeText(messageElement.innerText).then(() => {
        showMessage('Mensaje copiado al portapapeles', 'success');
    });
}

/**
 * Hacer scroll automático hacia abajo en el chat
 */
function scrollToBottom() {
    const chatContainer = document.getElementById('chat-container');
    chatContainer.scrollTop = chatContainer.scrollHeight;
}

/**
 * Guardar conversaciones en cache local (localStorage)
 */
function saveConversationsToCache() {
    try {
        localStorage.setItem('smartfood_conversations_cache', JSON.stringify({
            conversations: conversations,
            currentConversationId: currentConversationId,
            conversationCounter: conversationCounter,
            lastSync: new Date().toISOString()
        }));
    } catch (error) {
        console.error('Error guardando en cache:', error);
    }
}

/**
 * Cargar conversaciones desde cache local (fallback)
 */
function loadConversationsFromCache() {
    const cached = localStorage.getItem('smartfood_conversations_cache');
    if (cached) {
        try {
            const data = JSON.parse(cached);
            conversations = data.conversations || {};
            currentConversationId = data.currentConversationId || null;
            conversationCounter = data.conversationCounter || 0;

            console.log('Cargadas desde cache:', Object.keys(conversations).length, 'conversaciones');

            // Recrear sidebar con conversaciones en cache
            document.getElementById('conversations-list').innerHTML = '';
            Object.values(conversations).forEach(conv => {
                addConversationToSidebar(conv.id, conv.name);
            });

            // Seleccionar conversación actual si existe
            if (currentConversationId && conversations[currentConversationId]) {
                selectConversation(currentConversationId);
            }
        } catch (error) {
            console.error('Error cargando desde cache:', error);
            conversations = {};
        }
    }
}

/**
 * Sincronizar mensajes con el servidor
 * @param {string} conversationId
 * @param {string} content
 * @param {string} type
 */
async function syncMessageToServer(conversationId, content, type) {
    // No sincronizar si es conversación local
    if (conversations[conversationId]?.isLocal) {
        return null;
    }

    try {
        const result = await apiRequest(`/chat/conversations/${conversationId}/messages`, {
            method: 'POST',
            body: JSON.stringify({
                content: content,
                type: type
            })
        });

        if (result.success) {
            return result.data;
        }
    } catch (error) {
        console.error('Error sincronizando mensaje:', error);
    }

    return null;
}

/**
 * Obtener perfil del usuario para personalizar chat
 */
async function fetchUserProfile() {
    try {
        const result = await apiRequest('/user/me');
        if (result.success) {
            // Guardar información del usuario para usar en el chat
            window.currentUser = result.data;

            // Actualizar UI con datos del usuario
            updateUserUI(result.data);
        }
    } catch (error) {
        console.error('Error fetching user profile:', error);
    }
}

/**
 * Actualizar interfaz de usuario en el sidebar
 * @param {Object} user - Datos del usuario
 */
function updateUserUI(user) {
    // Actualizar avatar con iniciales
    const avatar = generateAvatar(user.name, user.surname);
    const avatarElement = document.getElementById('user-avatar');
    if (avatarElement) {
        avatarElement.textContent = avatar;
    }

    // Actualizar nombre del usuario
    const nameElement = document.getElementById('user-name');
    if (nameElement) {
        nameElement.textContent = `${user.name} ${user.surname}`;
    }
}

/**
 * Mostrar mensaje temporal en la interfaz
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo de mensaje: 'success', 'error', 'warning'
 */
function showMessage(message, type = 'info') {
    // Crear elemento de notificación
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm transition-all duration-300 ${
        type === 'success' ? 'bg-green-50 border border-green-600 text-green-700' :
        type === 'error' ? 'bg-red-100 border border-red-600 text-red-600' :
        type === 'warning' ? 'bg-blue-100 border border-blue-600 text-blue-600' :
        'bg-blue-100 border border-blue-600 text-blue-600'
    }`;

    document.body.appendChild(notification);

    // Auto-ocultar después de 3 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

/**
 * Recargar una conversación específica desde el servidor
 * @param {string} conversationId - ID de la conversación a recargar
 */
async function reloadConversationFromServer(conversationId) {
    try {
        const result = await apiRequest(`/chat/conversations/${conversationId}`);

        if (result.success) {
            // Actualizar datos locales con los del servidor
            conversations[conversationId] = {
                id: result.data.id,
                name: result.data.name,
                messages: conversations[conversationId].messages || [], // Mantener mensajes locales
                created_at: result.data.created_at,
                updated_at: result.data.updated_at
            };

            // Actualizar sidebar
            updateConversationNameInSidebar(conversationId, result.data.name);
            saveConversationsToCache();

            console.log('Conversación recargada desde servidor:', conversationId);
            return true;
        }
    } catch (error) {
        console.error('Error recargando conversación desde servidor:', error);
    }

    return false;
}

console.log('Sistema de chat híbrido inicializado');
