<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\TaxClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxClassController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TaxClass::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = (int) $request->get('per_page', 50);
        $taxClasses = $query->orderBy('name')->paginate($perPage);

        return response()->json($taxClasses);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            TaxClass::where('is_default', true)->update(['is_default' => false]);
        }

        $taxClass = TaxClass::create($validated);

        return response()->json([
            'message' => 'Tax class created successfully.',
            'tax_class' => $taxClass,
        ], 201);
    }

    public function show(TaxClass $taxClass): JsonResponse
    {
        return response()->json(['tax_class' => $taxClass]);
    }

    public function update(Request $request, TaxClass $taxClass): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If this is set as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            TaxClass::where('id', '!=', $taxClass->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $taxClass->update($validated);

        return response()->json([
            'message' => 'Tax class updated successfully.',
            'tax_class' => $taxClass,
        ]);
    }

    public function destroy(TaxClass $taxClass): JsonResponse
    {
        $taxClass->delete();

        return response()->json(['message' => 'Tax class deleted successfully.']);
    }
}
