<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Attribute::with('values');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $perPage = (int) $request->get('per_page', 15);
        $attributes = $query->orderBy('name')->paginate($perPage);

        return response()->json($attributes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:attributes,slug',
            'values' => 'nullable|array',
            'values.*' => 'string|max:255',
        ]);

        $attribute = Attribute::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? null,
        ]);

        // Create initial values if provided
        if (!empty($validated['values'])) {
            foreach ($validated['values'] as $value) {
                $attribute->values()->create(['value' => $value]);
            }
        }

        return response()->json([
            'message' => 'Attribute created successfully.',
            'attribute' => $attribute->load('values'),
        ], 201);
    }

    public function show(Attribute $attribute): JsonResponse
    {
        return response()->json([
            'attribute' => $attribute->load('values'),
        ]);
    }

    public function update(Request $request, Attribute $attribute): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:attributes,slug,' . $attribute->id,
            'values' => 'nullable|array',
            'values.*' => 'string|max:255',
        ]);

        $attribute->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? $attribute->slug,
        ]);

        // Sync values if provided
        if (isset($validated['values'])) {
            // Delete old values and create new ones
            $attribute->values()->delete();
            foreach ($validated['values'] as $value) {
                $attribute->values()->create(['value' => $value]);
            }
        }

        return response()->json([
            'message' => 'Attribute updated successfully.',
            'attribute' => $attribute->load('values'),
        ]);
    }

    public function destroy(Attribute $attribute): JsonResponse
    {
        $attribute->delete();

        return response()->json([
            'message' => 'Attribute deleted successfully.',
        ]);
    }

    // Manage individual values
    public function addValue(Request $request, Attribute $attribute): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
        ]);

        $value = $attribute->values()->create($validated);

        return response()->json([
            'message' => 'Value added successfully.',
            'value' => $value,
        ], 201);
    }

    public function removeValue(Attribute $attribute, AttributeValue $value): JsonResponse
    {
        if ($value->attribute_id !== $attribute->id) {
            return response()->json([
                'message' => 'Value does not belong to this attribute.',
            ], 422);
        }

        $value->delete();

        return response()->json([
            'message' => 'Value removed successfully.',
        ]);
    }
}
