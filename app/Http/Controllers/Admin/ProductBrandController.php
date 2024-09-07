<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\ProductBrandsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductBrandRequest;
use App\Models\ProductBrand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductBrandController extends Controller
{
    /**
     * Renders the index view for the ProductBrandsDataTable.
     *
     * @param ProductBrandsDataTable $dataTable
     * @return mixed
     */
    public function index(ProductBrandsDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.productBrands.index');
    }

    /**
     * Display the create form for a new product brand.
     *
     * @return \Illuminate\View\View
     */
    public function create(): View
    {
        return view('admin.productBrands.create');
    }

    /**
     * Store a new product brand.
     *
     * @param ProductBrandRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProductBrandRequest $request)
    {
        ProductBrand::create($request->validated());

        toastr('Product brand created successfully.', 'success');

        return to_route('admin.product-brands.index');
    }

    /**
     * Displays the edit view for a product brand.
     *
     * @param ProductBrand $productBrand
     * @return \Illuminate\View\View
     */
    public function edit(ProductBrand $productBrand): View
    {
        $productBrand->loadCount('products');

        return view('admin.productBrands.edit', compact('productBrand'));
    }

    /**
     * Updates a product brand with the given request data.
     *
     * @param ProductBrandRequest $request
     * @param ProductBrand $productBrand
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProductBrandRequest $request, ProductBrand $productBrand): RedirectResponse
    {
        $productBrand->update($request->validated());

        toastr('Product brand updated successfully.', 'success');

        return back();
    }

    /**
     * Deletes a product brand from the database.
     *
     * @param ProductBrand $productBrand
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(ProductBrand $productBrand): JsonResponse
    {
        $productBrand->delete();

        return response()->json(['message' => 'Product brand deleted successfully.']);
    }

    /**
     * Deletes multiple product brands based on the provided request.
     *
     * @param ProductBrandRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function massDestroy(ProductBrandRequest $request): JsonResponse
    {
        ProductBrand::whereIn('id', $request->validated('ids'))->delete();

        return response()->json(['message' => 'Product brands deleted successfully.']);
    }
}
