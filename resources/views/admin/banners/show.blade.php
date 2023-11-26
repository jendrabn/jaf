@extends('layouts.admin')
@section('content')
  <div class="card">
    <div class="card-header">
      {{ __('Show') }} {{ __('Banners') }}
    </div>

    <div class="card-body">
      <div class="form-group">
        <div class="form-group">
          <a class="btn btn-default"
            href="{{ route('admin.banners.index') }}">
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
                {{ $banner->id }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Image') }}
              </th>
              <td>
                @if ($banner->image)
                  <a href="{{ $banner->image->getUrl() }}"
                    style="display: inline-block"
                    target="_blank">
                    <img src="{{ $banner->image->getUrl('thumb') }}">
                  </a>
                @endif
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Image Alt') }}
              </th>
              <td>
                {{ $banner->image_alt }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Url') }}
              </th>
              <td>
                {{ $banner->url }}
              </td>
            </tr>
          </tbody>
        </table>
        <div class="form-group">
          <a class="btn btn-default"
            href="{{ route('admin.banners.index') }}">
            {{ __('Back to list') }}
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection
