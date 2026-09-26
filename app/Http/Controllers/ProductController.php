<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Product::active()
            ->with('category')
            ->orderBy('created_at', 'desc');

        // 1. Filter by Category
        if ($request->has('category_slug')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category_slug);
            });
        }

        // 2. Search (Name or Description)
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            // Using whereRaw for full-text search if supported, otherwise simple LIKE
            // Adjust based on your DB driver. Here is a generic LIKE approach for safety:
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhere('short_description', 'like', "%{$searchTerm}%");
            });
        }

        // 3. Filter by Certifications (Multi-select)
        // Expected format: ?certifications[]=organic&certifications[]=non_gmo
        if ($request->has('certifications')) {
            foreach ($request->input('certifications') as $cert) {
                $query->hasCertification($cert);
            }
        }

        // 4. Pagination
        $perPage = $request->input('per_page', 24);
        $products = $query->paginate($perPage);

        return inertia('products/index', [
            'products' => $products,
            'filters' => [
                'search' => $request->search,
                'category' => $request->category_slug,
                'certifications' => $request->certifications ?? [],
            ],
            'categories' => Category::query()
                ->where('is_active', true)
                ->get(['id', 'name', 'slug']),
        ]);
    }
}
