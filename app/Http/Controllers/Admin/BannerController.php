<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BannersDataTable;
use App\Http\Controllers\Controller;
use App\Traits\MediaUploadingTrait;
use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class BannerController extends Controller
{
    use MediaUploadingTrait;

    public function index(BannersDataTable $dataTable)
    {
        return $dataTable->render("admin.banners.index");
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(BannerRequest $request)
    {
        $banner = Banner::create($request->validated());

        if ($request->input('image', false)) {
            $banner->addMedia(storage_path('tmp/uploads/' . basename($request->input('image'))))->toMediaCollection(Banner::MEDIA_COLLECTION_NAME);
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $banner->id]);
        }

        return redirect()->route('admin.banners.index');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(BannerRequest $request, Banner $banner)
    {
        $banner->update($request->validated());

        if ($request->input('image', false)) {
            if (!$banner->image || $request->input('image') !== $banner->image->file_name) {
                if ($banner->image) {
                    $banner->image->delete();
                }
                $banner->addMedia(storage_path('tmp/uploads/' . basename($request->input('image'))))->toMediaCollection(Banner::MEDIA_COLLECTION_NAME);
            }
        } elseif ($banner->image) {
            $banner->image->delete();
        }

        return redirect()->route('admin.banners.index');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully.']);
    }

    public function massDestroy(BannerRequest $request)
    {
        Banner::whereIn('id', $request->validated('ids'))->delete();

        return response()->json(['message' => 'Banners deleted successfully.']);
    }

    public function storeCKEditorImages(Request $request)
    {
        $model = new Banner();
        $model->id = $request->input('crud_id', 0);
        $model->exists = true;
        $media = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
