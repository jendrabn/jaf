<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class BannerController extends Controller
{
    use MediaUploadingTrait;

    public function index()
    {
        $banners = Banner::with(['media'])->get();

        return view('admin.banners.index', compact('banners'));
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

        return back();
    }

    public function massDestroy(BannerRequest $request)
    {
        $banners = Banner::find($request->validated('ids'));

        foreach ($banners as $banner) {
            $banner->delete();
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeCKEditorImages(Request $request)
    {
        $model         = new Banner();
        $model->id     = $request->input('crud_id', 0);
        $model->exists = true;
        $media         = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json(['id' => $media->id, 'url' => $media->getUrl()], Response::HTTP_CREATED);
    }
}
