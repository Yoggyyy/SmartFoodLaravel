/**
 * SmartFood - Chat con IA
 * Manejo del chat inteligente para generar listas de compras
 */

// Variables globales del chat
let conversations = {};
let currentConversationId = null;
let conversationCounter = 0;
let messageCounter = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Cargar conversaciones guardadas del localStorage
    loadSavedConversations();

    // Si no hay conversaciones, crear la primera
    if (Object.keys(conversations).length === 0) {
        createNewConversation();
    }

    // Configurar eventos del chat
    document.getElementById('new-list-btn').addEventListener('click', createNewConversation);
    document.getElementById('message-form').addEventListener('submit', sendMessage);
    document.getElementById('message-input').addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage(e);
        }
    });

    // Obtener información del usuario para personalizar el chat
    fetchUserProfile();
});

/**
 * Crear nueva conversación vacía
 */
function createNewConversation() {
    conversationCounter++;
    const conversationId = `conv_${conversationCounter}`;
    const conversationName = `Lista de compra ${conversationCounter}`;

    // Crear objeto de conversación
    conversations[conversationId] = {
        id: conversationId,
        name: conversationName,
        messages: [],
        created_at: new Date().toISOString()
    };

    // Agregar al sidebar y seleccionar
    addConversationToSidebar(conversationId, conversationName);
    selectConversation(conversationId);
    saveConversations();
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
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <span class="text-sm text-gray-700 truncate">${conversationName}</span>
            </div>
            <button onclick="deleteConversation('${conversationId}', event)"
                    class="delete-btn opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 text-xs transition-opacity"
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
    currentConversationId = conversationId;

    // Actualizar UI del sidebar (quitar selección anterior)
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('bg-green-100', 'border-l-4', 'border-green-500');
    });

    // Marcar conversación actual como seleccionada
    const selectedItem = document.querySelector(`[data-conversation-id="${conversationId}"]`);
    if (selectedItem) {
        selectedItem.classList.add('bg-green-100', 'border-l-4', 'border-green-500');
    }

    // Limpiar y cargar mensajes de la conversación
    const chatContainer = document.getElementById('chat-container');
    chatContainer.innerHTML = '';

    if (conversations[conversationId]) {
        conversations[conversationId].messages.forEach(message => {
            if (message.type === 'user') {
                addUserMessageToDOM(message.content, message.id);
            } else {
                addBotMessageToDOM(message.content, message.id);
            }
        });
        scrollToBottom();
    }

    saveConversations();
}

/**
 * Eliminar conversación
 * @param {string} conversationId - ID de la conversación a eliminar
 * @param {Event} event - Evento del click para prevenir propagación
 */
function deleteConversation(conversationId, event) {
    event.stopPropagation();

    // No permitir eliminar si es la única conversación
    if (Object.keys(conversations).length <= 1) {
        showMessage('No puedes eliminar la última conversación', 'error');
        return;
    }

    // Eliminar de objeto y DOM
    delete conversations[conversationId];
    document.querySelector(`[data-conversation-id="${conversationId}"]`).remove();

    // Si era la conversación activa, seleccionar otra
    if (currentConversationId === conversationId) {
        const firstConversationId = Object.keys(conversations)[0];
        selectConversation(firstConversationId);
    }

    saveConversations();
    showMessage('Conversación eliminada', 'success');
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
    addUserMessage(message);

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
            addBotMessage(result.data.bot_message);

            // Log para debugging (opcional)
            console.log('Tokens usados:', result.data.tokens_used);
            console.log('Respuesta desde caché:', result.data.cached);
        } else {
            // Mostrar error
            addBotMessage('Lo siento, hubo un error al procesar tu mensaje. Por favor, inténtalo de nuevo.');
            console.error('Error de API:', result.message);
        }

    } catch (error) {
        hideTypingIndicator();
        addBotMessage('Error de conexión. Por favor, verifica tu conexión a internet e inténtalo de nuevo.');
        console.error('Error de red:', error);
    }
}

/**
 * Mostrar indicador de "escribiendo..." del bot
 */
function showTypingIndicator() {
    const typingHtml = `
        <div class="flex flex-col items-start gap-1" id="typing-indicator">
            <div class="flex items-start gap-3">
                <img src="/images/logoManzana.webp" alt="Bot" class="h-8 w-8 rounded-full" />
                <div class="bg-white rounded-lg p-4 shadow text-gray-800 max-w-xl">
                    <div class="flex items-center gap-1">
                        <span>SmartFood está escribiendo</span>
                        <div class="flex gap-1 ml-2">
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                            <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                        </div>
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
 * Agregar mensaje del usuario a la conversación
 * @param {string} message - Texto del mensaje
 */
function addUserMessage(message) {
    if (!currentConversationId) return;

    messageCounter++;
    const messageId = `user_${messageCounter}`;

    // Agregar al objeto de conversación
    conversations[currentConversationId].messages.push({
        id: messageId,
        type: 'user',
        content: message,
        timestamp: new Date().toISOString()
    });

    // Agregar al DOM y guardar
    addUserMessageToDOM(message, messageId);
    saveConversations();
    scrollToBottom();
}

/**
 * Agregar mensaje del bot a la conversación
 * @param {string} message - Texto del mensaje
 */
function addBotMessage(message) {
    if (!currentConversationId) return;

    messageCounter++;
    const messageId = `bot_${messageCounter}`;

    // Agregar al objeto de conversación
    conversations[currentConversationId].messages.push({
        id: messageId,
        type: 'bot',
        content: message,
        timestamp: new Date().toISOString()
    });

    // Agregar al DOM y guardar
    addBotMessageToDOM(message, messageId);
    saveConversations();
    scrollToBottom();
}

/**
 * Crear elemento visual del mensaje del usuario
 * @param {string} message - Texto del mensaje
 * @param {string} messageId - ID único del mensaje
 */
function addUserMessageToDOM(message, messageId) {
    const messageHtml = `
        <div class="flex flex-col items-end gap-1">
            <div class="flex items-start gap-3 justify-end w-full">
                <div class="bg-green-700 text-white rounded-lg p-4 shadow max-w-xl ml-auto" id="${messageId}">
                    ${escapeHtml(message)}
                </div>
            </div>
            <button class="flex items-center gap-1 text-green-700 hover:text-green-900 text-xs mr-2" onclick="copyMessage('${messageId}')">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a1 1 0 011 1v3M9 7h4' />
                </svg>
                Copiar
            </button>
        </div>
    `;

    document.getElementById('chat-container').insertAdjacentHTML('beforeend', messageHtml);
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
                <img src="/images/logoManzana.webp" alt="Bot" class="h-8 w-8 rounded-full" />
                <div class="bg-white rounded-lg p-4 shadow text-gray-800 max-w-xl" id="${messageId}">
                    ${escapeHtml(message)}
                </div>
            </div>
            <button class="flex items-center gap-1 text-green-700 hover:text-green-900 text-xs ml-11" onclick="copyMessage('${messageId}')">
                <svg xmlns='http://www.w3.org/2000/svg' class='h-4 w-4' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a1 1 0 011 1v3M9 7h4' />
                </svg>
                Copiar
            </button>
        </div>
    `;

    document.getElementById('chat-container').insertAdjacentHTML('beforeend', messageHtml);
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
 * Guardar conversaciones en localStorage
 */
function saveConversations() {
    localStorage.setItem('smartfood_conversations', JSON.stringify({
        conversations: conversations,
        currentConversationId: currentConversationId,
        conversationCounter: conversationCounter
    }));
}

/**
 * Cargar conversaciones guardadas del localStorage
 */
function loadSavedConversations() {
    const saved = localStorage.getItem('smartfood_conversations');
    if (saved) {
        try {
            const data = JSON.parse(saved);
            conversations = data.conversations || {};
            currentConversationId = data.currentConversationId || null;
            conversationCounter = data.conversationCounter || 0;

            // Recrear sidebar con conversaciones guardadas
            Object.values(conversations).forEach(conv => {
                addConversationToSidebar(conv.id, conv.name);
            });

            // Seleccionar conversación actual si existe
            if (currentConversationId && conversations[currentConversationId]) {
                selectConversation(currentConversationId);
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
        }
    }
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
