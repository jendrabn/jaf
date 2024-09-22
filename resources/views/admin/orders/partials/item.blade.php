@foreach ($items as $item)
    <div class="d-flex align-items-center justify-content-between mb-1">
        <div class="d-flex align-items-center">
            <div>
                <img class="mr-1"
                     src="{{ $item->product?->image->preview_url }}"
                     style="width: 35px; height: 35px; object-fit: cover;">
            </div>
            {{ $item->name }}
        </div>
        <div>x{{ $item->quantity }}</div>
    </div>
@endforeach
