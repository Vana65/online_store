<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategorySearchRequest;
use App\Http\Requests\Category\CategorySearchRequest;
use App\Http\Requests\Category\CategoryStoreRequest;
use App\Http\Requests\Category\CategoryUpdateRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(CategorySearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $categories = Category::query()
            ->when(isset($validated['search']), function ($query) use ($validated) {
                $query->where('name', 'like', '%'.$validated['search'].'%');
            })
            ->latest()
            ->get();

        return response()->json($categories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'],
        ]);

        return response()->json([
            'message' => __('keywords.category_created_success'),
            'data' => $category,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category): JsonResponse
    {
        return response()->json($category);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryUpdateRequest $request, Category $category): JsonResponse
    {

        $validated = $request->validated();

        $category->update([
            'name' => $validated['name'] ?? $category->name,
            'slug' => $validated['name']
                ? Str::slug($validated['name'])
                : $category->slug,
            'description' => $validated['description'] ?? $category->description,
        ]);

        return response()->json([
            'message' => __('keywords.Category_updated_successfully'),
            'data' => $category,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => __('keywords.Category_deleted__successfully'),
        ]);
    }
}
