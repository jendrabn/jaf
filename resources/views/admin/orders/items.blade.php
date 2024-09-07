@foreach ($items as $item)
    <div class="d-flex align-items-center justify-content-between mb-1">
        <div class="d-flex align-items-center">
            <div class="mr-1">
                <div style="width: 35px; overflow: hidden;">
                    <img class="img-fluid w-100 h-100"
                         src="{{ $item->product?->image->preview_url }}"
                         style="object-fit: cover;">
                </div>
            </div>

            <a href="{{ $item->product_id ? route('admin.products.show', $item->product_id) : 'javascript:;' }}"
               target="_blank">
                {{ $item->name }}
            </a>
        </div>
        <div>x{{ $item->quantity }}</div>
    </div>
@endforeach
