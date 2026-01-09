<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Fit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Fit::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = (int) $request->get('per_page', 50);
        $fits = $query->orderBy('name')->paginate($perPage);

        return response()->json($fits);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'nullable|string|max:50|unique:fits,slug',
            'is_active' => 'boolean',
        ]);

        $fit = Fit::create($validated);

        return response()->json([
            'message' => 'Fit created successfully.',
            'fit' => $fit,
        ], 201);
    }

    public function show(Fit $fit): JsonResponse
    {
        return response()->json(['fit' => $fit]);
    }

    public function update(Request $request, Fit $fit): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'nullable|string|max:50|unique:fits,slug,' . $fit->id,
            'is_active' => 'boolean',
        ]);

        $fit->update($validated);

        return response()->json([
            'message' => 'Fit updated successfully.',
            'fit' => $fit,
        ]);
    }

    public function destroy(Fit $fit): JsonResponse
    {
        $fit->delete();

        return response()->json(['message' => 'Fit deleted successfully.']);
    }
}
