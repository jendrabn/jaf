@foreach ($row->items as $item)
  <div class="d-flex mb-1">
    <div class="d-flex">
      @if ($item->product?->image)
        <div class="mr-1">
          <a href="{{ $item->product->image->getUrl() }}"
            style="display: inline-block"
            target="_blank"
            rel="noopener noreferrer">
            <img src="{{ $item->product->image->getUrl('thumb') }}">
          </a>
        </div>
      @endif
      <div>
        <a href="{{ route('admin.products.show', [$item->product_id]) }}"
          target="_blank"
          rel="noopener noreferrer">{{ $item->name }}</a>
      </div>
    </div>
    <div class="ml-auto">x{{ $item->quantity }}</div>
  </div>
@endforeach
