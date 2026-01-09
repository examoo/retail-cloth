<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Fabric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FabricController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Fabric::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = (int) $request->get('per_page', 50);
        $fabrics = $query->orderBy('name')->paginate($perPage);

        return response()->json($fabrics);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:fabrics,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $fabric = Fabric::create($validated);

        return response()->json([
            'message' => 'Fabric created successfully.',
            'fabric' => $fabric,
        ], 201);
    }

    public function show(Fabric $fabric): JsonResponse
    {
        return response()->json(['fabric' => $fabric]);
    }

    public function update(Request $request, Fabric $fabric): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:100|unique:fabrics,slug,' . $fabric->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $fabric->update($validated);

        return response()->json([
            'message' => 'Fabric updated successfully.',
            'fabric' => $fabric,
        ]);
    }

    public function destroy(Fabric $fabric): JsonResponse
    {
        $fabric->delete();

        return response()->json(['message' => 'Fabric deleted successfully.']);
    }
}
