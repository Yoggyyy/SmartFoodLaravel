# 🍎 SmartFood - Generador Inteligente de Listas de Compra

**SmartFood** es una aplicación web inteligente que utiliza IA (OpenAI GPT) para generar listas de compra personalizadas basadas en las preferencias alimentarias y alérgenos de cada usuario.

## ✨ Características Principales

- 🤖 **Chat con IA**: Genera listas de compra personalizadas usando OpenAI
- 👤 **Gestión de Usuarios**: Registro, login y gestión de perfiles
- 🚫 **Manejo de Alérgenos**: Sistema completo de alérgenos por usuario
- 💬 **Conversaciones Múltiples**: Mantén varias listas de compra simultáneamente
- 🎨 **Modo Oscuro**: Interfaz adaptable con tema claro/oscuro
- 📱 **Responsive**: Diseño adaptado para móviles y escritorio
- 🔒 **Seguridad**: Autenticación con Laravel Sanctum y protección CSRF

## 🛠️ Tecnologías Utilizadas

### Backend
- **PHP 8.x** con **Laravel 11.x**
- **MySQL** como base de datos
- **Laravel Sanctum** para autenticación
- **OpenAI API** para generación de contenido con IA

### Frontend
- **HTML5**, **CSS3**, **JavaScript ES6+**
- **Tailwind CSS** para estilos
- **Blade Templates** de Laravel
- **AJAX** para comunicación asíncrona

### Herramientas de Desarrollo
- **Composer** para dependencias PHP
- **NPM/Node.js** para assets frontend
- **Git** para control de versiones

## 📋 Requisitos del Sistema

- PHP >= 8.1
- Composer
- Node.js >= 16.x
- MySQL >= 8.0
- Cuenta de OpenAI API

## 🚀 Instalación

### 1. Clonar el repositorio
```bash
git clone https://github.com/tu-usuario/smartfood-laravel.git
cd smartfood-laravel
```

### 2. Instalar dependencias PHP
```bash
composer install
```

### 3. Instalar dependencias Node.js
```bash
npm install
```

### 4. Configurar variables de entorno
```bash
cp .env.example .env
```

Edita el archivo `.env` con tu configuración:
```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartfood
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_contraseña

# OpenAI API
OPENAI_API_KEY=tu_api_key_de_openai
OPENAI_MODEL=gpt-3.5-turbo
```

### 5. Generar clave de aplicación
```bash
php artisan key:generate
```

### 6. Ejecutar migraciones y seeders
```bash
php artisan migrate --seed
```

### 7. Compilar assets
```bash
npm run build
```

### 8. Iniciar servidor de desarrollo
```bash
php artisan serve
```

La aplicación estará disponible en `http://localhost:8000`

## 📁 Estructura del Proyecto

```
SmartFoodLaravel/
├── app/
│   ├── Http/Controllers/     # Controladores
│   │   ├── AuthController.php
│   │   └── ChatController.php
│   ├── Models/              # Modelos Eloquent
│   │   ├── User.php
│   │   └── Allergen.php
│   └── Services/            # Servicios
│       └── OpenAIService.php
├── public/js/               # JavaScript Frontend
│   ├── common.js           # Funciones utilitarias
│   ├── chat.js             # Chat con IA
│   ├── profile.js          # Gestión de perfil
│   ├── settings.js         # Configuraciones
│   └── auth/               # Autenticación
│       ├── login.js
│       └── register.js
├── resources/
│   └── views/              # Vistas Blade
│       ├── auth/           # Autenticación
│       ├── chat.blade.php  # Chat principal
│       └── profile.blade.php
└── routes/
    ├── web.php             # Rutas web
    └── api.php             # Rutas API
```

## 🎯 Funcionalidades Implementadas

### Autenticación y Usuarios
- ✅ Registro de usuarios con alérgenos
- ✅ Inicio de sesión seguro
- ✅ Gestión de perfiles
- ✅ Cambio de contraseñas
- ✅ Cierre de sesión

### Chat Inteligente
- ✅ Conversaciones con IA usando OpenAI
- ✅ Múltiples listas de compra simultáneas
- ✅ Contexto personalizado por usuario
- ✅ Historial de conversaciones
- ✅ Copia de mensajes al portapapeles

### Configuraciones
- ✅ Modo oscuro/claro
- ✅ Configuración de notificaciones
- ✅ FAQ interactivo
- ✅ Persistencia de configuraciones

## 🔧 Configuración de OpenAI

1. Crea una cuenta en [OpenAI](https://platform.openai.com/)
2. Genera una API Key
3. Añade la clave en tu archivo `.env`:
```env
OPENAI_API_KEY=sk-tu-api-key-aqui
OPENAI_MODEL=gpt-3.5-turbo
```

## 📊 Base de Datos

### Tablas Principales
- `users` - Usuarios de la aplicación
- `allergens` - Catálogo de alérgenos
- `user_allergens` - Relación usuarios-alérgenos
- `personal_access_tokens` - Tokens de autenticación

### Seeders Incluidos
- Alérgenos comunes (gluten, lactosa, frutos secos, etc.)
- Usuario administrador de prueba

## 🐛 Testing

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test --filter AuthTest
```

## 📈 Mejoras Futuras

- [ ] Integración con APIs de supermercados
- [ ] Notificaciones push
- [ ] Exportación de listas a PDF
- [ ] Compartir listas entre usuarios
- [ ] Análisis nutricional
- [ ] Integración con calendarios
- [ ] App móvil nativa

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/nueva-funcionalidad`)
3. Commit tus cambios (`git commit -m 'Añadir nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

**Tu Nombre**
- GitHub: [@tu-usuario](https://github.com/tu-usuario)
- Email: tu-email@ejemplo.com

## 🙏 Agradecimientos

- [Laravel](https://laravel.com/) por el excelente framework
- [OpenAI](https://openai.com/) por la API de IA
- [Tailwind CSS](https://tailwindcss.com/) por los estilos
- Comunidad de desarrolladores de Laravel

---

⭐ **¡Dale una estrella al proyecto si te parece útil!** ⭐
