<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
  use MediaUploadingTrait;

  public function index(Request $request)
  {
    if ($request->ajax()) {
      $model = Product::with(['category', 'brand', 'media'])->select('products.*');

      $table = Datatables::eloquent($model)->filter(
        function ($q) use ($request) {
          $q->when(
            $request->filled('category_id'),
            fn ($q) => $q->where('product_category_id', $request->get('category_id'))
          );

          $q->when(
            $request->filled('brand_id'),
            fn ($q) => $q->where('product_brand_id', $request->get('brand_id'))
          );

          $q->when(
            $request->filled('sex'),
            fn ($q) => $q->where('sex', $request->get('sex'))
          );

          $q->when(
            $request->filled('is_publish'),
            fn ($q) => $q->where('is_publish', $request->get('is_publish'))
          );
        },
        true
      );

      $table->addColumn('placeholder', '&nbsp;');
      $table->addColumn('actions', '&nbsp;');

      $table->editColumn('actions', function ($row) {
        $crudRoutePart = 'products';

        return view('partials.datatablesActions', compact(
          'crudRoutePart',
          'row'
        ));
      });

      $table->editColumn('id', function ($row) {
        return $row->id ? $row->id : '';
      });
      $table->editColumn('image', function ($row) {
        if (!$row->image) {
          return '';
        }
        return '<a href="' . $row->image->getUrl() . '" target="_blank"><img src="' . $row->image->getUrl('thumb') . '" width="50px" height="50px"></a>';
      });
      $table->editColumn('name', function ($row) {
        return $row->name ? $row->name : '';
      });
      $table->addColumn('category_name', function ($row) {
        return $row->category ? $row->category->name : '';
      });

      $table->addColumn('brand_name', function ($row) {
        return $row->brand ? $row->brand->name : '';
      });

      $table->editColumn('sex', function ($row) {
        return $row->sex ? Product::SEX_SELECT[$row->sex] : '';
      });
      $table->editColumn('price', function ($row) {
        return $row->price ? 'Rp ' . number_format((float) $row->price, 0, ',', '.') : '';
      });
      $table->editColumn('stock', function ($row) {
        return $row->stock ? $row->stock : '';
      });
      $table->editColumn('weight', function ($row) {
        return $row->weight ? $row->weight : '';
      });
      $table->editColumn('is_publish', function ($row) {
        return '<input type="checkbox" disabled ' . ($row->is_publish ? 'checked' : null) . '>';
      });

      $table->rawColumns(['actions', 'placeholder', 'image', 'is_publish']);

      return $table->make(true);
    }
    $product_categories = ProductCategory::pluck('name', 'id')->prepend('All', null);

    $product_brands = ProductBrand::pluck('name', 'id')->prepend('All', null);

    return view('admin.products.index', compact('product_brands', 'product_categories'));
  }

  public function create()
  {
    $product_categories = ProductCategory::pluck('name', 'id')->prepend('Please select', null);

    $product_brands = ProductBrand::pluck('name', 'id')->prepend('Please select', null);

    return view('admin.products.create', compact('product_brands', 'product_categories'));
  }

  public function store(ProductRequest $request)
  {
    $validatedData = $request->validated();
    $validatedData['slug'] = str($validatedData['name'] . '-' . (Product::latest()->first()->id ?? 0) + 1)->slug();

    $product = Product::create($validatedData);

    foreach ($request->input('images', []) as $file) {
      $product->addMedia(storage_path('tmp/uploads/' . basename($file)))->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
    }

    if ($media = $request->input('ck-media', false)) {
      Media::whereIn('id', $media)->update(['model_id' => $product->id]);
    }

    return redirect()->route('admin.products.index');
  }

  public function edit(Product $product)
  {
    $product_categories = ProductCategory::pluck('name', 'id')->prepend('Please select', null);

    $product_brands = ProductBrand::pluck('name', 'id')->prepend('Please select', null);

    $product->load('category', 'brand');

    return view('admin.products.edit', compact('product', 'product_brands', 'product_categories'));
  }

  public function update(ProductRequest $request, Product $product)
  {
    $validatedData = $request->validated();
    $validatedData['slug'] = str($validatedData['name'] . '-' . (Product::latest()->first()->id ?? 0) + 1)->slug();

    $product->update($validatedData);

    if (count($product->images) > 0) {
      foreach ($product->images as $media) {
        if (!in_array($media->file_name, $request->input('images', []))) {
          $media->delete();
        }
      }
    }
    $media = $product->images->pluck('file_name')->toArray();
    foreach ($request->input('images', []) as $file) {
      if (count($media) === 0 || !in_array($file, $media)) {
        $product->addMedia(storage_path('tmp/uploads/' . basename($file)))->toMediaCollection(Product::MEDIA_COLLECTION_NAME);
      }
    }

    return redirect()->route('admin.products.index');
  }

  public function show(Product $product)
  {
    $product->load('category', 'brand');

    return view('admin.products.show', compact('product'));
  }

  public function destroy(Product $product)
  {
    $product->delete();

    return back();
  }

  public function massDestroy(ProductRequest $request)
  {
    $products = Product::find($request->validated('ids'));

    foreach ($products as $product) {
      $product->delete();
    }

    return response(null, Response::HTTP_NO_CONTENT);
  }

  public function storeCKEditorImages(Request $request)
  {
    $model         = new Product();
    $model->id     = $request->input('crud_id', 0);
    $model->exists = true;
    $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

    return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
  }
}
