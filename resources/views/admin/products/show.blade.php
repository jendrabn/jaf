@extends('layouts.admin')
@section('content')
  <div class="card">
    <div class="card-header">
      {{ __('Show') }} {{ __('Products') }}
    </div>

    <div class="card-body">
      <div class="form-group">
        <div class="form-group">
          <a class="btn btn-default"
            href="{{ route('admin.products.index') }}">
            {{ __('Back to list') }}
          </a>
        </div>
        <table class="table-bordered table-striped table">
          <tbody>
            <tr>
              <th>
                {{ __('ID') }}
              </th>
              <td>
                {{ $product->id }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Product Images') }}
              </th>
              <td>
                @foreach ($product->images as $key => $media)
                  <a href="{{ $media->getUrl() }}"
                    style="display: inline-block"
                    target="_blank">
                    <img src="{{ $media->getUrl('thumb') }}">
                  </a>
                @endforeach
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Product Name') }}
              </th>
              <td>
                {{ $product->name }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Product Slug') }}
              </th>
              <td>
                {{ $product->slug }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Category') }}
              </th>
              <td>
                {{ $product->category->name ?? '' }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Product Description') }}
              </th>
              <td>
                {!! $product->description !!}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Brand') }}
              </th>
              <td>
                {{ $product->brand->name ?? '' }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Gender') }}
              </th>
              <td>
                {{ App\Models\Product::SEX_SELECT[$product->sex] ?? '' }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Price') }}
              </th>
              <td>
                @rupiah($product->price)
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Stock') }}
              </th>
              <td>
                {{ $product->stock }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Weight') }}
              </th>
              <td>
                {{ $product->weight }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Publish') }}
              </th>
              <td>
                <input type="checkbox"
                  disabled="disabled"
                  {{ $product->is_publish ? 'checked' : '' }}>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="form-group">
          <a class="btn btn-default"
            href="{{ route('admin.products.index') }}">
            {{ __('Back to list') }}
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection
