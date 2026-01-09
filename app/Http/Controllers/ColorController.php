<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Color::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = (int) $request->get('per_page', 50);
        $colors = $query->orderBy('name')->paginate($perPage);

        return response()->json($colors);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'nullable|string|max:50|unique:colors,slug',
            'hex_code' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'color_code' => 'nullable|string|max:3',
            'is_active' => 'boolean',
        ]);

        $color = Color::create($validated);

        return response()->json([
            'message' => 'Color created successfully.',
            'color' => $color,
        ], 201);
    }

    public function show(Color $color): JsonResponse
    {
        return response()->json(['color' => $color]);
    }

    public function update(Request $request, Color $color): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'nullable|string|max:50|unique:colors,slug,' . $color->id,
            'hex_code' => 'nullable|string|max:7|regex:/^#[a-fA-F0-9]{6}$/',
            'color_code' => 'nullable|string|max:3',
            'is_active' => 'boolean',
        ]);

        $color->update($validated);

        return response()->json([
            'message' => 'Color updated successfully.',
            'color' => $color,
        ]);
    }

    public function destroy(Color $color): JsonResponse
    {
        $color->delete();

        return response()->json(['message' => 'Color deleted successfully.']);
    }
}
