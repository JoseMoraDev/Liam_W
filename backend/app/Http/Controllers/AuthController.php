<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Registro
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'font_id'   => $request->font_id ?? 1,
            'color_id'  => $request->color_id ?? 1,
        ]);

        return response()->json([
            'message' => 'Usuario creado',
            'user'    => $user
        ], 201);
    }

    // Login
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'email'    => 'required|email',
    //         'password' => 'required|string'
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         return response()->json(['message' => 'Credenciales incorrectas'], 401);
    //     }

    //     $token = $user->createToken('api-token')->plainTextToken;

    //     return response()->json([
    //         'message' => 'Login correcto',
    //         'token'   => $token,
    //         'user'    => $user
    //     ]);
    // }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string'
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Credenciales incorrectas'], 401);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'message' => 'Login correcto',
                'token'   => $token,
                'user'    => $user
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error inesperado',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout correcto']);
    }

    // User Info
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // Recuperar contraseña (simulación)
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        // Aquí normalmente generarías token y enviarías correo
        return response()->json([
            'message' => 'Se ha enviado un correo para restablecer la contraseña'
        ]);
    }
}
