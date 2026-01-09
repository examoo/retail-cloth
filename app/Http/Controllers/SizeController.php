<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SizeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Size::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('size_group')) {
            $query->where('size_group', $request->size_group);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = (int) $request->get('per_page', 50);
        $sizes = $query->orderBy('sort_order')->orderBy('name')->paginate($perPage);

        return response()->json($sizes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'nullable|string|max:50|unique:sizes,slug',
            'size_group' => 'required|in:baby,kids,adult,custom',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $size = Size::create($validated);

        return response()->json([
            'message' => 'Size created successfully.',
            'size' => $size,
        ], 201);
    }

    public function show(Size $size): JsonResponse
    {
        return response()->json(['size' => $size]);
    }

    public function update(Request $request, Size $size): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'slug' => 'nullable|string|max:50|unique:sizes,slug,' . $size->id,
            'size_group' => 'required|in:baby,kids,adult,custom',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $size->update($validated);

        return response()->json([
            'message' => 'Size updated successfully.',
            'size' => $size,
        ]);
    }

    public function destroy(Size $size): JsonResponse
    {
        $size->delete();

        return response()->json(['message' => 'Size deleted successfully.']);
    }
}
