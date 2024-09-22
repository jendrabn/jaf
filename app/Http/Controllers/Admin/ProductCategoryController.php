<?php

namespace App\Http\Controllers\Admin;

use App\Traits\MediaUploadingTrait;
use Illuminate\View\View;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\DataTables\ProductCategoriesDataTable;
use App\Http\Requests\Admin\ProductCategoryRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductCategoryController extends Controller
{
    use MediaUploadingTrait;

    /**
     * Handles the display of the product categories index page.
     *
     * @param ProductCategoriesDataTable $dataTable
     * @return mixed
     */
    public function index(ProductCategoriesDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.productCategories.index');
    }

    /**
     * Display the form for creating a new product category.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('admin.productCategories.create');
    }

    /**
     * Handles the creation of a new product category.
     *
     * @param ProductCategoryRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProductCategoryRequest $request)
    {
        $productCategory = ProductCategory::create($request->validated());

        if ($request->input('logo', false)) {
            $productCategory->addMedia(storage_path('tmp/uploads/' . basename($request->input('logo'))))
                ->toMediaCollection(ProductCategory::MEDIA_COLLECTION_NAME);
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $productCategory->id]);
        }

        toastr('Product category created successfully.', 'success');

        return to_route('admin.product-categories.index');
    }

    /**
     * Edit a product category.
     *
     * @param ProductCategory $productCategory
     * @return \Illuminate\View\View
     */
    public function edit(ProductCategory $productCategory)
    {
        $productCategory->loadCount('products');

        return view('admin.productCategories.edit', compact('productCategory'));
    }

    /**
     * Updates a product category based on the provided request data.
     *
     * @param ProductCategoryRequest $request
     * @param ProductCategory $productCategory
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProductCategoryRequest $request, ProductCategory $productCategory)
    {
        $productCategory->update($request->validated());

        if ($request->input('logo', false)) {
            if (!$productCategory->logo || $request->input('logo') !== $productCategory->logo->file_name) {
                if ($productCategory->logo) {
                    $productCategory->logo->delete();
                }

                $productCategory->addMedia(storage_path('tmp/uploads/' . basename($request->input('logo'))))
                    ->toMediaCollection(ProductCategory::MEDIA_COLLECTION_NAME);
            }
        } elseif ($productCategory->logo) {
            $productCategory->logo->delete();
        }

        toastr('Product category updated successfully.', 'success');

        return back();
    }


    /**
     * Deletes a product category.
     *
     * @param ProductCategory $productCategory
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ProductCategory $productCategory)
    {
        $productCategory->delete();

        return response()->json(['message' => 'Product category deleted successfully.']);
    }

    /**
     * Deletes multiple product categories based on the provided IDs.
     *
     * @param ProductCategoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function massDestroy(ProductCategoryRequest $request): JsonResponse
    {
        ProductCategory::whereIn('id', $request->validated('ids'))->delete();

        return response()->json(['message' => 'Product categories deleted successfully.']);
    }
}
