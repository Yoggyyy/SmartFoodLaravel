<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Allergen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Controlador de Autenticación para SmartFood
 *
 * Este controlador maneja todas las operaciones relacionadas con la autenticación
 * de usuarios, incluyendo registro, inicio de sesión, gestión de perfiles y
 * funcionalidades tanto para API como para aplicación web.
 *
 * Funcionalidades implementadas:
 * - Registro de usuarios con alérgenos (RF1)
 * - Inicio de sesión con autenticación de tokens/sesiones (RF2)
 * - Gestión de perfiles de usuario (RF3)
 * - Cambio de contraseñas (RF4)
 * - Cierre de sesión seguro (RF14)
 * - Verificación de email
 * - Recuperación de contraseñas
 *
 * @package App\Http\Controllers
 * @author Tu Nombre
 * @version 1.0
 * @since 1.0.0
 */
class AuthController extends Controller
{
    /**
     * Registrar un nuevo usuario en el sistema (RF1)
     *
     * Este método permite el registro de nuevos usuarios validando sus datos,
     * creando el usuario en la base de datos, asociando alérgenos si los hay,
     * enviando email de verificación y generando token de autenticación.
     *
     * @param Request $request Datos del usuario a registrar
     * @return JsonResponse Respuesta JSON con datos del usuario y token
     *
     * @throws \Exception Si ocurre un error durante el proceso de registro
     *
     * Body Parameters:
     * - name (required|string|max:255): Nombre del usuario
     * - surname (required|string|max:255): Apellido del usuario
     * - email (required|email|unique): Email único del usuario
     * - password (required|string|min:8|confirmed): Contraseña segura
     * - preferences (nullable|string): Preferencias alimentarias del usuario
     * - allergens (nullable|array): Array de IDs de alérgenos
     *
     * @example
     * POST /api/auth/register
     * {
     *   "name": "Juan",
     *   "surname": "Pérez",
     *   "email": "juan@email.com",
     *   "password": "password123",
     *   "password_confirmation": "password123",
     *   "allergens": [1, 3, 5]
     * }
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // Validación exhaustiva de datos de entrada según reglas de negocio
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'preferences' => 'nullable|string',
                'allergens' => 'nullable|array',
                'allergens.*' => 'exists:allergens,id'
            ], [
                // Mensajes de error personalizados en español
                'email.unique' => 'Este correo electrónico ya está registrado.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'allergens.*.exists' => 'Uno o más alérgenos seleccionados no son válidos.'
            ]);

            // Retornar errores de validación si existen
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Crear nuevo usuario con datos validados y contraseña hasheada
            $user = User::create([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Hash seguro de la contraseña
                'preferences' => $request->preferences,
            ]);

            // Asociar alérgenos del usuario si se proporcionaron
            if ($request->has('allergens') && is_array($request->allergens)) {
                $user->allergens()->attach($request->allergens);
            }

            // Disparar evento de registro para envío de email de verificación
            event(new Registered($user));

            // Generar token de autenticación usando Laravel Sanctum
            $token = $user->createToken('auth_token')->plainTextToken;

            // Respuesta exitosa con datos del usuario y token
            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente. Por favor, verifica tu correo electrónico.',
                'data' => [
                    'user' => $user->load('allergens'), // Incluir alérgenos en la respuesta
                    'token' => $token
                ]
            ], 201);

        } catch (\Exception $e) {
            // Log del error para debugging (opcional: Log::error())
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Iniciar sesión de usuario (RF2)
     *
     * Autentica a un usuario existente validando sus credenciales,
     * regenerando la sesión por seguridad y devolviendo un token
     * de autenticación para acceso a rutas protegidas.
     *
     * @param Request $request Credenciales de acceso del usuario
     * @return JsonResponse Respuesta JSON con datos del usuario autenticado y token
     *
     * @throws \Exception Si ocurre un error durante la autenticación
     *
     * Body Parameters:
     * - email (required|email): Email del usuario registrado
     * - password (required|string): Contraseña del usuario
     * - remember (optional|boolean): Mantener sesión activa
     *
     * @example
     * POST /api/auth/login
     * {
     *   "email": "juan@email.com",
     *   "password": "password123",
     *   "remember": true
     * }
     */
    public function login(Request $request): JsonResponse
    {
        try {
            // Validación de formato de credenciales
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
                'remember' => 'boolean'
            ], [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo electrónico no es válido.',
                'password.required' => 'La contraseña es obligatoria.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Intentar autenticación con las credenciales proporcionadas
            if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas. Por favor, verifica tu email y contraseña.'
                ], 401);
            }

            // Regenerar ID de sesión para prevenir ataques de session fixation
            $request->session()->regenerate();

            $user = Auth::user();

            // Implementar verificación de email más adelante
            /*
            // Verificar si el email está verificado
            if (!$user->hasVerifiedEmail()) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Por favor, verifica tu correo electrónico antes de iniciar sesión.',
                    'requires_verification' => true
                ], 403);
            }
            */

            // Generar nuevo token de autenticación
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'data' => [
                    'user' => $user->load('allergens'),
                    'token' => $token
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cerrar sesión del usuario autenticado (RF14)
     *
     * Revoca el token de acceso actual del usuario para cerrar
     * su sesión de forma segura y prevenir accesos no autorizados.
     *
     * @param Request $request Request con token de autenticación
     * @return JsonResponse Confirmación de cierre de sesión
     *
     * @throws \Exception Si ocurre un error al revocar el token
     *
     * Headers requeridos:
     * - Authorization: Bearer {token}
     *
     * @example
     * POST /api/auth/logout
     * Headers: { "Authorization": "Bearer token_aqui" }
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            // Revocar únicamente el token actual del usuario
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        try {
            $user = User::find($request->route('id'));

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no encontrado'
                ], 404);
            }

            /*
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => true,
                    'message' => 'El correo electrónico ya está verificado'
                ], 200);
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }
            */

            return response()->json([
                'success' => true,
                'message' => 'Correo electrónico verificado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en la verificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Solicitar recuperación de contraseña (RF2)
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email'
            ], [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo electrónico no es válido.',
                'email.exists' => 'No encontramos una cuenta con este correo electrónico.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'success' => true,
                    'message' => 'Enlace de recuperación enviado. Revisa tu correo electrónico.'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar el enlace de recuperación. Inténtalo de nuevo.'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restablecer contraseña (RF2)
     */
    public function resetPassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed'
            ], [
                'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->save();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => 'Contraseña restablecida exitosamente'
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Token inválido o expirado'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reenviar email de verificación
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => true,
                    'message' => 'El correo electrónico ya está verificado'
                ], 200);
            }

            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Email de verificación reenviado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al reenviar email',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuario autenticado
     */
    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user()->load('allergens');

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar perfil de usuario
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'preferences' => 'nullable|string'
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'surname.required' => 'Los apellidos son obligatorios.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo electrónico no es válido.',
                'email.unique' => 'Este correo electrónico ya está en uso por otro usuario.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar datos del usuario
            $user->update([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'preferences' => $request->preferences,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'data' => [
                    'user' => $user->load('allergens')
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed'
            ], [
                'current_password.required' => 'La contraseña actual es obligatoria.',
                'password.required' => 'La nueva contraseña es obligatoria.',
                'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar contraseña actual
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta.'
                ], 400);
            }

            // Actualizar contraseña
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login para web (usando sesiones)
     */
    public function webLogin(Request $request)
    {
        try {
            // Validación de credenciales
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ], [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo electrónico no es válido.',
                'password.required' => 'La contraseña es obligatoria.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar credenciales
            if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas. Por favor, verifica tu email y contraseña.'
                ], 401);
            }

            // Regenerar sesión para seguridad
            $request->session()->regenerate();

            $user = Auth::user();

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'data' => [
                    'user' => $user->load('allergens')
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register para web (usando sesiones)
     */
    public function webRegister(Request $request)
    {
        try {
            // Validación de datos de entrada
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'preferences' => 'nullable|string',
                'allergens' => 'nullable|array',
                'allergens.*' => 'exists:allergens,id'
            ], [
                'email.unique' => 'Este correo electrónico ya está registrado.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'allergens.*.exists' => 'Uno o más alérgenos seleccionados no son válidos.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Crear usuario
            $user = User::create([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'preferences' => $request->preferences,
            ]);

            // Asociar alérgenos si se proporcionan
            if ($request->has('allergens') && is_array($request->allergens)) {
                $user->allergens()->attach($request->allergens);
            }

            // Enviar email de verificación
            event(new Registered($user));

            // Iniciar sesión automáticamente después del registro
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente. Por favor, verifica tu correo electrónico.',
                'data' => [
                    'user' => $user->load('allergens')
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout para web (usando sesiones)
     */
    public function webLogout(Request $request)
    {
        try {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener usuario autenticado (Web)
     */
    public function webMe(Request $request)
    {
        try {
            $user = $request->user()->load('allergens');

            return response()->json([
                'success' => true,
                'data' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar perfil de usuario (Web)
     */
    public function webUpdateProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'preferences' => 'nullable|string'
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'surname.required' => 'Los apellidos son obligatorios.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo electrónico no es válido.',
                'email.unique' => 'Este correo electrónico ya está en uso por otro usuario.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar datos del usuario
            $user->update([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'preferences' => $request->preferences,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'data' => $user->load('allergens')
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar contraseña (Web)
     */
    public function webChangePassword(Request $request)
    {
        try {
            $user = $request->user();

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed'
            ], [
                'current_password.required' => 'La contraseña actual es obligatoria.',
                'password.required' => 'La nueva contraseña es obligatoria.',
                'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Verificar contraseña actual
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'La contraseña actual es incorrecta.'
                ], 400);
            }

            // Actualizar contraseña
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar contraseña',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
