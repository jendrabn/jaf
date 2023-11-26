<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductCategoryRequest;
use App\Models\ProductCategory;
use Symfony\Component\HttpFoundation\Response;

class ProductCategoryController extends Controller
{
  public function index()
  {
    $productCategories = ProductCategory::all();

    return view('admin.productCategories.index', compact('productCategories'));
  }

  public function create()
  {
    return view('admin.productCategories.create');
  }

  public function store(ProductCategoryRequest $request)
  {
    $validatedData = $request->validated();
    $validatedData['slug'] = str($validatedData['name'])->slug();

    $productCategory = ProductCategory::create($validatedData);

    return redirect()->route('admin.product-categories.index');
  }

  public function edit(ProductCategory $productCategory)
  {
    return view('admin.productCategories.edit', compact('productCategory'));
  }

  public function update(ProductCategoryRequest $request, ProductCategory $productCategory)
  {
    $validatedData = $request->validated();
    $validatedData['slug'] = str($validatedData['name'])->slug();

    $productCategory->update($validatedData);

    return redirect()->route('admin.product-categories.index');
  }

  public function destroy(ProductCategory $productCategory)
  {
    $productCategory->delete();

    return back();
  }

  public function massDestroy(ProductCategoryRequest $request)
  {
    $productCategories = ProductCategory::find($request->validated('ids'));

    foreach ($productCategories as $productCategory) {
      $productCategory->delete();
    }

    return response(null, Response::HTTP_NO_CONTENT);
  }
}
