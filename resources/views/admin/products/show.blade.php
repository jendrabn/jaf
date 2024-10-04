@extends('layouts.admin', ['title' => 'Show Product'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">Show Product</h1>
        </div>
        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.products.index') }}">Back to list</a>

            <table class="table table-bordered table-sm table-striped">
                <tr>
                    <th>ID</th>
                    <td>{{ $product->id }}</td>
                </tr>

                <tr>
                    <th>Product Name</th>
                    <td>{{ $product->name }}</td>
                </tr>

                <tr>
                    <th>Slug</th>
                    <td>{{ $product->slug }}</td>
                </tr>

                <tr>
                    <th>Images</th>
                    <td>
                        @foreach ($product->images as $image)
                            <a href="{{ $image->url }}"
                               target="_blank">
                                <img class="m-1"
                                     src="{{ $image->preview_url }}"
                                     style="width: 150px; height: 150px; object-fit: cover" />
                            </a>
                        @endforeach
                    </td>
                </tr>

                <tr>
                    <th>Category</th>
                    <td>{{ $product->category?->name }}</td>
                </tr>

                <tr>
                    <th>Brand</th>
                    <td>{{ $product->brand?->name }}</td>
                </tr>

                <tr>
                    <th>Weight</th>
                    <td>{{ $product->weight }} gram</td>
                </tr>

                <tr>
                    <th>Price</th>
                    <td>@Rp($product->price)</td>
                </tr>

                <tr>
                    <th>Gender</th>
                    <td>{{ $product->sex_label }}</td>
                </tr>

                <tr>
                    <th>Description</th>
                    <td>{!! $product->description !!}</td>
                </tr>

                <tr>
                    <th>Published</th>
                    <td>
                        <input {{ $product->is_publish ? 'checked' : '' }}
                               onclick="return false;"
                               type="checkbox" />
                    </td>
                </tr>

                <tr>
                    <th>Created At</th>
                    <td>{{ $product->created_at }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection
