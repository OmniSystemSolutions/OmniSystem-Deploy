<?php

namespace App\Http\Controllers;

use App\Models\BranchProduct;
use App\Models\Category;
use App\Models\Component;
use App\Models\Product;
use App\Models\Station;
use App\Models\Subcategory;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Models\BranchComponent;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
{
    return view('products.index');
}

/**
 * Fetch products based on status, search, pagination, and branch
 */
    public function fetchProducts(Request $request)
    {
    // 🔥 DEFAULT STATUS HANDLED HERE
    $status = $request->get('status', 'active');

        $perPage     = $request->get('perPage', 10);
        $search      = $request->get('search');
        $category    = $request->get('category');
        $subcategory = $request->get('subcategory');
        $type       = $request->get('type');

        $branchId = current_branch_id();

        $products = Product::query()
            ->select([
                'products.*',
                'bc.quantity',
                'bc.price',
                'bc.status as status'
            ])
            ->join('branch_products as bc', 'bc.product_id', '=', 'products.id')
            ->where('bc.branch_id', $branchId)
            ->where('products.status', $status)
            ->with(['category', 'subcategory', 'unit', 'station'])
            ->when($type, fn ($q) =>
                    $q->where('products.type', $type)
                )
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('products.name', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($q) =>
                            $q->where('name', 'like', "%{$search}%")
                        )
                        ->orWhereHas('subcategory', fn ($q) =>
                            $q->where('name', 'like', "%{$search}%")
                        );
                });
            })
            ->when($category, fn ($q) =>
                $q->where('products.category_id', $category)
            )
            ->when($subcategory, fn ($q) =>
                $q->where('products.subcategory_id', $subcategory)
            )
            ->orderBy('products.created_at', 'desc')
            ->paginate($perPage);

        return response()->json($products);
    }



    public function create()
    {
        $categories = Category::where('status', 'active')->get();
        $subcategories = Subcategory::all();
        $units = Unit::all();
        $stations = Station::all();

        $branch = current_branch_id(); // current branch

        $components = BranchComponent::with('component.unit')
            ->where('branch_id', $branch)
            ->get()
            ->map(function($bc) {
                return [
                    'id' => $bc->component_id,
                    'name' => $bc->component->name,
                    'unit' => $bc->component->unit->name ?? null,
                    'cost' => $bc->cost,
                ];
            });

        return view('products.create', compact(
            'categories',
            'subcategories',
            'units',
            'stations',
            'components'
        ));
    }

   public function store(Request $request)
{
    $validated = $request->validate([
        'code' => 'required|string|unique:products,code',
        'name' => 'required|string',
        'price' => 'required|numeric',
        'quantity' => 'required|numeric|min:0',
        'station_id' => 'required|exists:stations,id',
        'unit_id' => 'required|exists:units,id',
        'category_id' => 'required|exists:categories,id',
        'subcategory_id' => 'nullable|exists:subcategories,id',
        'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        'recipes' => ['required', 'array', 'min:1'],
        'recipes.*.component_id' => 'required|exists:components,id',
        'recipes.*.quantity' => 'required|numeric|min:1',
    ]);

    if ($request->hasFile('image')) {
        $validated['image'] = $request->file('image')->store('products', 'public');
    }

    $product = Product::create($validated);

    BranchProduct::create([
        'branch_id' => current_branch_id(),
        'product_id' => $product->id,
        'station_id' => $product->station_id,
        'unit_id' => $product->unit_id,
        'quantity' => $product->quantity,
        'price' => $product->price,
        'status' => 'active',
    ]);

    // Save recipes
    if ($request->has('recipes')) {
        foreach ($request->recipes as $recipeData) {
            if (empty($recipeData['component_id']) || empty($recipeData['quantity'])) continue;

            $product->recipes()->create([
                'component_id' => $recipeData['component_id'],
                'quantity' => $recipeData['quantity'],
            ]);
        }
    }

    return redirect()->route('products.index')->with('success', 'Product created with recipes.');
}

    public function edit($id)
{
    $branch = current_branch_id();

    $branchProduct = BranchProduct::with(['product.recipes.component.unit'])
        ->where('product_id', $id)
        ->where('branch_id', $branch)
        ->firstOrFail();

    $product = $branchProduct->product;

    $oldRecipes = $product->recipes->map(function ($r) use ($branch) {

        $branchComponent = BranchComponent::where('component_id', $r->component_id)
            ->where('branch_id', $branch)
            ->first();

        $cost = $branchComponent->cost ?? 0;

        return [
            'id' => $r->id,
            'component_id' => $r->component_id,
            'quantity' => $r->quantity,
            'unit' => optional($r->component->unit)->name ?? null,
            'cost' => $r->quantity * $cost,
        ];
    })->toArray();

    $categories = Category::where('status', 'active')->get();
    $subcategories = Subcategory::all();

    $components = BranchComponent::with(['component.unit:id,name'])
        ->where('branch_id', $branch)
        ->get()
        ->map(fn($bc) => [
            'id' => $bc->component_id,
            'name' => $bc->component->name,
            'unit' => $bc->component->unit,
            'cost' => $bc->cost
        ]);

    $units = Unit::all();
    $stations = Station::all();

    return view('products.edit', compact(
        'product',
        'branchProduct',
        'categories',
        'subcategories',
        'components',
        'units',
        'stations',
        'oldRecipes'
    ));
}

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|unique:products,code,' . $product->id,
            'name' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',

            'station_id' => 'required|exists:stations,id',
            'quantity' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',

            'recipes' => 'nullable|array',
            'recipes.*.component_id' => 'required|exists:components,id',
            'recipes.*.quantity' => 'required|numeric',
            'recipes.*.unit' => 'required|string',
            'recipes.*.cost' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
                $productData = Arr::only($validated, [
                'code',
                'name',
                'price',
                'category_id',
                'subcategory_id',
                'station_id',
                'unit_id',
                'quantity',
            ]);

            // Handle new image upload
            if ($request->hasFile('image')) {
                // delete old image if exists
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }
                $productData['image'] = $request->file('image')->store('products', 'public');
            }

            $product->update($productData);

            if ($request->has('recipes')) {
                $existingIds = $product->recipes()->pluck('id')->toArray();
                $submittedIds = [];

                foreach ($validated['recipes'] as $recipeData) {
                    if (!empty($recipeData['id'])) {
                        $recipe = $product->recipes()->find($recipeData['id']);
                        if ($recipe) {
                            $recipe->update($recipeData);
                            $submittedIds[] = $recipe->id;
                        }
                    } else {
                        $newRecipe = $product->recipes()->create($recipeData);
                        $submittedIds[] = $newRecipe->id;
                    }
                }

                $toDelete = array_diff($existingIds, $submittedIds);
                if (!empty($toDelete)) {
                    $product->recipes()->whereIn('id', $toDelete)->delete();
                }
            }

            DB::commit();
            return redirect()->route('products.index')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => 'Failed to update product: ' . $e->getMessage()]);
        }
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Move the specified Product to archive (status change).
     */
    public function archive(Product $product)
{
    $product->update(['status' => 'archived']);

    return response()->json([
        'message' => 'Product moved to archive successfully.',
        'status' => 'success',
        'product_id' => $product->id
    ]);
}


    /**
     * Restore a product from archive.
     */
    public function restore(Product $product)
{
    $product->update([
        'status' => 'active'
    ]);

    return response()->json([
        'message' => 'Product restored to active successfully.',
        'status' => 'success',
        'product_id' => $product->id
    ]);
}

   public function verify(Request $request)
    {
       $request->validate([
            'rows' => 'required|array',
        ]);

        $rows = $request->rows;
        $branchId = current_branch_id(); // get current branch

        $errors = [];

        foreach ($rows as $index => $row) {
            $code = $row['code'] ?? null;
            $name = $row['name'] ?? null;

            if (!$code && !$name) continue;

            // Check if product exists in BranchProduct for this branch
            $exists = BranchProduct::whereHas('product', function($q) use ($code, $name) {
                if ($code) $q->where('code', $code);
                if ($name) $q->orWhere('name', $name);
            })
            ->where('branch_id', $branchId)
            ->exists();

            if ($exists) {
                $errors[$index] = "Duplicate SKU or Name found in this branch";
            }
        }

        return response()->json(['errors' => $errors]);
    }


    public function import(Request $request)
    {
        $request->validate([
            'rows' => 'required'
        ]);

        $rows = json_decode($request->rows, true);
        $branchId = current_branch_id();

        DB::beginTransaction();

        try {

            foreach ($rows as $row) {

                /*
                |--------------------------------------------------------------------------
                | 1️⃣ CREATE OR GET CATEGORY
                |--------------------------------------------------------------------------
                */

                $category = null;

                if (!empty($row['category']['name'])) {
                    $category = Category::firstOrCreate(
                        ['name' => $row['category']['name']],
                        ['status' => 'active']
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | 2️⃣ CREATE OR GET SUBCATEGORY
                |--------------------------------------------------------------------------
                */

                $subcategory = null;

                if (!empty($row['subcategory']['name']) && $category) {
                    $subcategory = SubCategory::firstOrCreate(
                        [
                            'name' => $row['subcategory']['name'],
                            'category_id' => $category->id
                        ],
                        ['status' => 'active']
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | 3️⃣ CREATE OR GET UNIT
                |--------------------------------------------------------------------------
                */

                $unit = null;

                if (!empty($row['unit']['name'])) {
                    $unit = Unit::firstOrCreate(
                        ['name' => $row['unit']['name']]
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | 4️⃣ CREATE OR UPDATE PRODUCT
                |--------------------------------------------------------------------------
                */

                $product = Product::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'category_id' => $category?->id,
                        'subcategory_id' => $subcategory?->id,
                        'status' => 'active'
                    ]
                );

                /*
                |--------------------------------------------------------------------------
                | 5️⃣ CREATE OR UPDATE BRANCH PRODUCT
                |--------------------------------------------------------------------------
                */

                BranchProduct::updateOrCreate(
                    [
                        'branch_id'  => $branchId,
                        'product_id' => $product->id,
                    ],
                    [
                        'unit_id'   => $unit?->id,
                        'quantity'  => $row['quantity'] ?? 0,
                        'cost'      => $row['cost'] ?? 0,
                        'price'     => $row['price'] ?? 0,
                        'status'    => 'active',
                        'type'      => 'simple',
                    ]
                );
            }

            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
