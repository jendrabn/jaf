<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\Admin\BankRequest;
use App\Models\Bank;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class BankController extends Controller
{
  use MediaUploadingTrait;

  public function index()
  {
    $banks = Bank::with(['media'])->get();

    return view('admin.banks.index', compact('banks'));
  }

  public function create()
  {
    return view('admin.banks.create');
  }

  public function store(BankRequest $request)
  {
    $bank = Bank::create($request->validated());

    if ($request->input('logo', false)) {
      $bank->addMedia(storage_path('tmp/uploads/' . basename($request->input('logo'))))->toMediaCollection(Bank::MEDIA_COLLECTION_NAME);
    }

    if ($media = $request->input('ck-media', false)) {
      Media::whereIn('id', $media)->update(['model_id' => $bank->id]);
    }

    return redirect()->route('admin.banks.index');
  }

  public function edit(Bank $bank)
  {
    return view('admin.banks.edit', compact('bank'));
  }

  public function update(BankRequest $request, Bank $bank)
  {
    $bank->update($request->validated());

    if ($request->input('logo', false)) {
      if (!$bank->logo || $request->input('logo') !== $bank->logo->file_name) {
        if ($bank->logo) {
          $bank->logo->delete();
        }
        $bank->addMedia(storage_path('tmp/uploads/' . basename($request->input('logo'))))->toMediaCollection(Bank::MEDIA_COLLECTION_NAME);
      }
    } elseif ($bank->logo) {
      $bank->logo->delete();
    }

    return redirect()->route('admin.banks.index');
  }

  public function show(Bank $bank)
  {
    return view('admin.banks.show', compact('bank'));
  }

  public function destroy(Bank $bank)
  {
    $bank->delete();

    return back();
  }

  public function massDestroy(BankRequest $request)
  {
    $banks = Bank::find($request->validated('ids'));

    foreach ($banks as $bank) {
      $bank->delete();
    }

    return response(null, Response::HTTP_NO_CONTENT);
  }

  public function storeCKEditorImages(Request $request)
  {
    $model         = new Bank();
    $model->id     = $request->input('crud_id', 0);
    $model->exists = true;
    $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

    return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
  }
}
