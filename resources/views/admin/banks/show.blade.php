@extends('layouts.admin')
@section('content')
  <div class="card">
    <div class="card-header">
      {{ __('Show') }} {{ __('Banks') }}
    </div>

    <div class="card-body">
      <div class="form-group">
        <div class="form-group">
          <a class="btn btn-default"
            href="{{ route('admin.banks.index') }}">
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
                {{ $bank->id }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Logo') }}
              </th>
              <td>
                @if ($bank->logo)
                  <a href="{{ $bank->logo->getUrl() }}"
                    style="display: inline-block"
                    target="_blank">
                    <img src="{{ $bank->logo->getUrl('thumb') }}">
                  </a>
                @endif
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Bank Name') }}
              </th>
              <td>
                {{ $bank->name }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Bank Code') }}
              </th>
              <td>
                {{ $bank->code }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Account Name') }}
              </th>
              <td>
                {{ $bank->account_name }}
              </td>
            </tr>
            <tr>
              <th>
                {{ __('Account Number') }}
              </th>
              <td>
                {{ $bank->account_number }}
              </td>
            </tr>
          </tbody>
        </table>
        <div class="form-group">
          <a class="btn btn-default"
            href="{{ route('admin.banks.index') }}">
            {{ __('Back to list') }}
          </a>
        </div>
      </div>
    </div>
  </div>
@endsection
