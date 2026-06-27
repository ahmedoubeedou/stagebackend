<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * 📌 تسجيل مستخدم جديد (Register)
     */
    public function register(Request $request)
    {
        // التحقق من البيانات
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        // إنشاء المستخدم
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // إنشاء Token (Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Utilisateur créé avec succès',
            'token' => $token,
            'user' => $user->makeHidden(['password'])
        ]);
    }

    /**
     * 📌 تسجيل الدخول (Login)
     */
    public function login(Request $request)
    {
        // التحقق من البيانات
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // البحث عن المستخدم
        $user = User::where('email', $data['email'])->first();

        // التحقق من كلمة المرور
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Email ou mot de passe incorrect'
            ], 401);
        }

        // إنشاء Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'token' => $token,
            'user' => $user->makeHidden(['password'])
        ]);
    }

    /**
     * 📌 تسجيل الخروج (Logout - current device)
     */
    public function logout(Request $request)
    {
        // حذف التوكن الحالي فقط
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie'
        ]);
    }

    /**
     * 📌 جلب المستخدم الحالي (Profile)
     */
    public function me(Request $request)
    {
        return response()->json([
            'message' => 'Utilisateur connecté',
            'user' => $request->user()
        ]);
    }
}