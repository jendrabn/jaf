<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductBrandRequest;
use App\Models\ProductBrand;
use Symfony\Component\HttpFoundation\Response;

class ProductBrandController extends Controller
{
  public function index()
  {
    $productBrands = ProductBrand::all();

    return view('admin.productBrands.index', compact('productBrands'));
  }

  public function create()
  {
    return view('admin.productBrands.create');
  }

  public function store(ProductBrandRequest $request)
  {
    $validatedData = $request->validated();
    $validatedData['slug'] = str($validatedData['name'])->slug();

    $productBrand = ProductBrand::create($validatedData);

    return redirect()->route('admin.product-brands.index');
  }

  public function edit(ProductBrand $productBrand)
  {
    return view('admin.productBrands.edit', compact('productBrand'));
  }

  public function update(ProductBrandRequest $request, ProductBrand $productBrand)
  {
    $validatedData = $request->validated();
    $validatedData['slug'] = str($validatedData['name'])->slug();

    $productBrand->update($validatedData);

    return redirect()->route('admin.product-brands.index');
  }

  public function destroy(ProductBrand $productBrand)
  {
    $productBrand->delete();

    return back();
  }

  public function massDestroy(ProductBrandRequest $request)
  {
    $productBrands = ProductBrand::find($request->validated('ids'));

    foreach ($productBrands as $productBrand) {
      $productBrand->delete();
    }

    return response(null, Response::HTTP_NO_CONTENT);
  }
}
