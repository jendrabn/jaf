<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\DataTables\BanksDataTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\BankRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\MediaUploadingTrait;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BankController extends Controller
{
    use MediaUploadingTrait;

    /**
     * Handles the index action for the bank controller.
     *
     * @param BanksDataTable $dataTable
     * @return mixed
     */
    public function index(BanksDataTable $dataTable): mixed
    {
        return $dataTable->render("admin.banks.index");
    }

    /**
     * Displays the view for creating a new bank entry.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.banks.create');
    }

    public function store(BankRequest $request): RedirectResponse
    {
        $bank = Bank::create($request->validated());

        if ($request->input('logo', false)) {
            $bank->addMedia(storage_path('tmp/uploads/' . basename($request->input('logo'))))->toMediaCollection(Bank::MEDIA_COLLECTION_NAME);
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $bank->id]);
        }

        toastr('Bank created successfully.', 'success');

        return to_route('admin.banks.index');
    }

    public function edit(Bank $bank): View
    {
        return view('admin.banks.edit', compact('bank'));
    }

    public function update(BankRequest $request, Bank $bank): RedirectResponse
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

        toastr('Bank updated successfully.', 'success');

        return back();
    }

    public function destroy(Bank $bank): JsonResponse
    {
        $bank->delete();

        return response()->json(['message' => 'Bank deleted successfully.']);
    }

    public function massDestroy(BankRequest $request): JsonResponse
    {
        Bank::whereIn('id', $request->validated('ids'))->delete();

        return response()->json(['message' => 'Bank deleted successfully.']);
    }

    public function storeCKEditorImages(Request $request): JsonResponse
    {
        $model = new Bank();
        $model->id = $request->input('crud_id', 0);
        $model->exists = true;
        $media = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
