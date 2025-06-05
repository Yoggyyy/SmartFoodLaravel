<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Allergen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
// FUNCIONALIDAD COMENTADA - FUTURA IMPLEMENTACIÓN
// use Illuminate\Auth\Events\Registered;
// use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Controlador de Autenticación para SmartFood
 *
 * Este controlador maneja todas las operaciones relacionadas con la autenticación
 * de usuarios, incluyendo registro, inicio de sesión, gestión de perfiles y
 * funcionalidades para aplicación web.
 *
 * Funcionalidades implementadas:
 * - Registro de usuarios con alérgenos (RF1)
 * - Inicio de sesión con autenticación de tokens/sesiones (RF2)
 * - Gestión de perfiles de usuario (RF3)
 * - Cambio de contraseñas (RF4)
 * - Cierre de sesión seguro (RF14)
 * - Recuperación de contraseñas
 *
 * FUNCIONALIDADES COMENTADAS (FUTURAS):
 * - Verificación de email (pendiente de implementar)
 *
 * @package App\Http\Controllers
 * @author Tu Nombre
 * @version 1.0
 * @since 1.0.0
 */
class AuthController extends Controller
{
    /**
     * FUNCIONALIDAD COMENTADA - FUTURA IMPLEMENTACIÓN
     * Verificar email
     */
    /*
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

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => true,
                    'message' => 'El correo electrónico ya está verificado'
                ], 200);
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

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
    */

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
     * FUNCIONALIDAD COMENTADA - FUTURA IMPLEMENTACIÓN
     * Reenviar email de verificación
     */
    /*
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
    */

    /**
     * Iniciar sesión web (Enfoque Híbrido)
     *
     * Este método maneja tanto peticiones AJAX como formularios tradicionales
     * para una mejor experiencia de usuario y accesibilidad.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            // Validación de credenciales
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
                'remember' => 'boolean'
            ], [
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo electrónico no es válido.',
                'password.required' => 'La contraseña es obligatoria.'
            ]);

            // Si hay errores de validación
            if ($validator->fails()) {
                // Para peticiones AJAX
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Para formularios tradicionales
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput($request->except('password'));
            }

            // Intentar autenticación
            $credentials = $request->only('email', 'password');
            $remember = $request->boolean('remember');

            if (!Auth::attempt($credentials, $remember)) {
                $errorMessage = 'Credenciales incorrectas. Por favor, verifica tu email y contraseña.';

                // Para peticiones AJAX
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 401);
                }

                // Para formularios tradicionales
                return redirect()->back()
                    ->withErrors(['email' => $errorMessage])
                    ->withInput($request->except('password'));
            }

            // Regenerar sesión por seguridad
            $request->session()->regenerate();

            $user = Auth::user();

            // TEMPORAL: Verificación de email desactivada
            /*
            // Verificar si el email está verificado
            if (!$user->hasVerifiedEmail()) {
                $verificationMessage = 'Tu email no está verificado. Por favor, verifica tu email para continuar.';

                // Para peticiones AJAX
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $verificationMessage,
                        'needs_verification' => true,
                        'user_id' => $user->id
                    ], 403);
                }

                // Para formularios tradicionales - logout y redirigir
                Auth::logout();
                return redirect()->back()
                    ->withErrors(['email' => $verificationMessage])
                    ->withInput($request->except('password'));
            }
            */

            $successMessage = '¡Inicio de sesión exitoso! Bienvenido/a de vuelta.';

            // Para peticiones AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'redirect_url' => route('chat'),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ], 200);
            }

            // Para formularios tradicionales
            return redirect()->intended(route('chat'))
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Error en login: ' . $e->getMessage());

            $errorMessage = 'Error interno del servidor. Por favor, inténtalo de nuevo.';

            // Para peticiones AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            // Para formularios tradicionales
            return redirect()->back()
                ->withErrors(['general' => $errorMessage])
                ->withInput($request->except('password'));
        }
    }

    /**
     * Registrar usuario web (Enfoque Híbrido)
     *
     * Este método maneja tanto peticiones AJAX como formularios tradicionales
     * para una mejor experiencia de usuario y accesibilidad en el registro.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            // Validación exhaustiva de datos de entrada
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'preferences' => 'nullable|string',
                'allergens' => 'nullable|array',
                'allergens.*' => 'exists:allergens,id'
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'surname.required' => 'Los apellidos son obligatorios.',
                'email.required' => 'El correo electrónico es obligatorio.',
                'email.email' => 'El formato del correo electrónico no es válido.',
                'email.unique' => 'Este correo electrónico ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'password.confirmed' => 'Las contraseñas no coinciden.',
                'allergens.*.exists' => 'Uno o más alérgenos seleccionados no son válidos.'
            ]);

            // Si hay errores de validación
            if ($validator->fails()) {
                // Para peticiones AJAX
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Error de validación',
                        'errors' => $validator->errors()
                    ], 422);
                }

                // Para formularios tradicionales
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput($request->except(['password', 'password_confirmation']));
            }

            // Crear nuevo usuario
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

            // FUNCIONALIDAD COMENTADA - FUTURA IMPLEMENTACIÓN
            // Enviar email de verificación (evento)
            // event(new Registered($user));

            // Iniciar sesión automáticamente después del registro
            Auth::login($user);

            // Regenerar sesión por seguridad
            $request->session()->regenerate();

            $successMessage = '¡Registro exitoso! Bienvenido/a a SmartFood.';

            // Para peticiones AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'redirect_url' => route('chat'),
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email
                    ]
                ], 201);
            }

            // Para formularios tradicionales
            return redirect()->intended(route('chat'))
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Error en register: ' . $e->getMessage());

            $errorMessage = 'Error interno del servidor. Por favor, inténtalo de nuevo.';

            // Para peticiones AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }

            // Para formularios tradicionales
            return redirect()->back()
                ->withErrors(['general' => $errorMessage])
                ->withInput($request->except(['password', 'password_confirmation']));
        }
    }

    /**
     * Logout para web (usando sesiones)
     */
    public function logout(Request $request)
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
    public function me(Request $request)
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
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Validación de datos (sin email)
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'surname' => 'required|string|max:255',
                'preferences' => 'nullable|string'
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'surname.required' => 'Los apellidos son obligatorios.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar datos del usuario (sin email)
            $user->update([
                'name' => $request->name,
                'surname' => $request->surname,
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
    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();

            // Validación de datos
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
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
                    'message' => 'La contraseña actual es incorrecta'
                ], 422);
            }

            // Actualizar contraseña
            $user->update([
                'password' => Hash::make($request->password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contraseña cambiada exitosamente'
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
     * Obtener estadísticas del usuario (Web)
     */
    public function userStats(Request $request)
    {
        try {
            $user = $request->user();

            // Obtener cantidad de listas del usuario
            $listsCount = $user->shoppingLists()->count();

            // Obtener fecha de la última actividad (última lista creada o última actualización de usuario)
            $lastListActivity = $user->shoppingLists()->latest('updated_at')->first();
            $lastActivity = $lastListActivity ? $lastListActivity->updated_at : $user->updated_at;

            $stats = [
                'member_since' => $user->created_at->toISOString(),
                'lists_count' => $listsCount,
                'last_activity' => $lastActivity->toISOString(),
                'total_products' => $user->shoppingLists()->withCount('products')->get()->sum('products_count'),
                'email_verified' => $user->hasVerifiedEmail(),
                'allergens_count' => $user->allergens()->count()
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas del usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
