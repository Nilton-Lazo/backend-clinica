<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'nombres' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'correo' => 'required|email|unique:usuarios',
            'contrasena' => 'required|string|min:8'
        ]);

        $usuarioGenerado = strtolower(substr($request->nombres, 0, 1) . $request->apellido_paterno);

        $usuario = Usuario::create([
            'nombres' => $request->nombres,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'usuario' => $usuarioGenerado,
            'correo' => $request->correo,
            'contrasena' => Hash::make($request->contrasena),
            'nivel' => 'usuario',
            'estado' => 'habilitado'
        ]);

        return response()->json([
            'message' => 'Usuario registrado con éxito',
            'usuario' => $usuario
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario' => 'required|string',
            'contrasena' => 'required|string',
        ]);

        $usuario = Usuario::where('usuario', $request->usuario)
            ->orWhere('correo', $request->usuario)
            ->first();

        if (!$usuario || !Hash::check($request->contrasena, $usuario->contrasena)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        if ($usuario->estado === 'inhabilitado') {
            return response()->json(['message' => 'Usuario inhabilitado'], 403);
        }

        return response()->json([
            'message' => 'Login exitoso',
            'usuario' => [
                'id' => $usuario->id,
                'nombre' => $usuario->nombres . ' ' . $usuario->apellido_paterno,
                'correo' => $usuario->correo,
                'nivel' => $usuario->nivel,
            ]
        ], 200);
    }
}
