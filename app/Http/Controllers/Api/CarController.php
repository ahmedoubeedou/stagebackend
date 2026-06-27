<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\Request;

class CarController extends Controller
{
    /**
     * 📌 جلب سيارات المستخدم الحالي
     */
    public function index(Request $request)
    {
        $cars = Car::with(['images', 'videos'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Liste de mes voitures',
            'data' => $cars
        ]);
    }

    /**
     * 📌 لوحة التحكم: جلب جميع السيارات
     */
    public function dashboard()
    {
        $cars = Car::with(['user', 'images', 'videos'])
            ->withCount(['images', 'videos'])
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Liste des voitures du dashboard',
            'data' => $cars
        ]);
    }

    /**
     * 📌 إضافة سيارة جديدة
     */
    public function store(Request $request)
    {
        // التحقق من البيانات
        $data = $request->validate([
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:' . date('Y'),
            'price' => 'required|numeric|min:0',
            'fuel_type' => 'nullable|string',
            'transmission' => 'nullable|string',
            'mileage' => 'nullable|integer',
            'color' => 'nullable|string',
            'condition' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        // إنشاء السيارة
        $car = Car::create([
            'user_id' => $request->user()->id,
            'status' => 'available',
            ...$data
        ]);

        return response()->json([
            'message' => 'Voiture créée avec succès',
            'car' => $car
        ]);
    }

    /**
     * 📌 عرض سيارة واحدة
     */
    public function show($id)
    {
        $car = Car::with(['user', 'images', 'videos'])
            ->where('id', $id)
            ->first();

        // إذا لم توجد السيارة
        if (!$car) {
            return response()->json([
                'message' => 'Voiture introuvable'
            ], 404);
        }

        return response()->json($car);
    }

    /**
     * 📌 تحديث سيارة (فقط المالك)
     */
    public function update(Request $request, $id)
    {
        // البحث عن السيارة الخاصة بالمستخدم
        $car = Car::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$car) {
            return response()->json([
                'message' => 'Non autorisé ou voiture introuvable'
            ], 404);
        }

        // تحديث البيانات
        $car->update($request->only([
            'brand',
            'model',
            'year',
            'price',
            'fuel_type',
            'transmission',
            'mileage',
            'color',
            'condition',
            'status',
            'description'
        ]));

        return response()->json([
            'message' => 'Voiture mise à jour avec succès',
            'car' => $car
        ]);
    }

    /**
     * 📌 حذف سيارة (فقط المالك)
     */
    public function destroy(Request $request, $id)
    {
        // التأكد أن السيارة تخص المستخدم
        $car = Car::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$car) {
            return response()->json([
                'message' => 'Non autorisé ou voiture introuvable'
            ], 404);
        }

        // حذف السيارة
        $car->delete();

        return response()->json([
            'message' => 'Voiture supprimée avec succès'
        ]);
    }
}