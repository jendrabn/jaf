<?php

namespace App\Http\Controllers\Admin;

use App\Models\Blog;
use App\Models\User;
use App\Models\BlogTag;
use Illuminate\View\View;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use App\DataTables\BlogDataTable;
use Illuminate\Http\JsonResponse;
use App\Traits\MediaUploadingTrait;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Admin\BlogRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class BlogController extends Controller
{
    use MediaUploadingTrait;

    /**
     * Display a listing of the blogs resource.
     *
     * @param BlogDataTable $dataTable
     * @return mixed
     */
    public function index(BlogDataTable $dataTable): mixed
    {
        $categories = BlogCategory::pluck('name', 'id')->prepend('All', null);
        $tags = BlogTag::pluck('name', 'id')->prepend('All', null);
        $authors = User::role(User::ROLE_ADMIN)->pluck('name', 'id')->prepend('All', null);

        return $dataTable->render('admin.blogs.index', compact(
            'categories',
            'tags',
            'authors'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        $categories = BlogCategory::pluck('name', 'id')->prepend('---', null);
        $tags = BlogTag::pluck('name', 'id')->prepend('---', null);
        $authors = User::role(User::ROLE_ADMIN)->pluck('name', 'id')->prepend('---', null);

        return view('admin.blogs.create', compact(
            'categories',
            'tags',
            'authors'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BlogRequest $request
     * @return RedirectResponse
     */
    public function store(BlogRequest $request): RedirectResponse
    {
        $blog = Blog::create($request->validated());

        $blog->tags()->attach($request->validated('tag_ids'));

        if ($request->input('featured_image', false)) {
            $blog->addMedia(storage_path('tmp/uploads/' . basename($request->input('featured_image'))))
                ->toMediaCollection(Blog::MEDIA_COLLECTION_NAME);
        }

        if ($media = $request->input('ck-media', false)) {
            Media::whereIn('id', $media)->update(['model_id' => $blog->id]);
        }

        toastr('Blog created successfully', 'success');

        return redirect()->route('admin.blogs.index');
    }

    /**
     * Display the specified blog resource.
     *
     * @param Blog $blog
     * @return View
     */
    public function show(Blog $blog): View
    {
        return view('admin.blogs.show', compact('blog'));
    }

    /**
     * Edit a blog resource.
     *
     * @param Blog $blog
     * @return View
     */
    public function edit(Blog $blog): View
    {
        $categories = BlogCategory::pluck('name', 'id')->prepend('---', null);
        $tags = BlogTag::pluck('name', 'id')->prepend('---', null);
        $authors = User::role(User::ROLE_ADMIN)->pluck('name', 'id')->prepend('---', null);

        return view('admin.blogs.edit', compact(
            'blog',
            'categories',
            'tags',
            'authors'
        ));
    }

    /**
     * Updates a blog resource with the provided request data.
     *
     * @param BlogRequest $request
     * @param Blog $blog
     * @return RedirectResponse
     */
    public function update(BlogRequest $request, Blog $blog): RedirectResponse
    {
        $blog->update($request->validated());
        $blog->tags()->sync($request->validated('tag_ids'));

        if ($request->input('featured_image', false)) {
            if (!$blog->featured_image || $request->input('featured_image') !== $blog->featured_image->file_name) {
                if ($blog->featured_image) {
                    $blog->featured_image->delete();
                }

                $blog->addMedia(storage_path('tmp/uploads/' . basename($request->input('featured_image'))))
                    ->toMediaCollection(Blog::MEDIA_COLLECTION_NAME);
            }
        } elseif ($blog->featured_image) {
            $blog->featured_image->delete();
        }

        toastr('Blog updated successfully', 'success');

        return back();
    }

    /**
     * Deletes a blog resource.
     *
     * @param Blog $blog
     * @return JsonResponse
     */
    public function destroy(Blog $blog): JsonResponse
    {
        $blog->delete();

        return response()->json(['message' => 'Blog deleted successfully.'], 200);
    }

    /**
     * Deletes multiple blogs from the database.
     *
     * @param BlogRequest $request
     * @return JsonResponse
     */
    public function massDestroy(BlogRequest $request): JsonResponse
    {
        Blog::whereIn('id', $request->validated('ids'))->delete();

        return response()->json(['message' => 'Blogs deleted successfully.'], 200);
    }

    /**
     * Stores images uploaded through CKEditor.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeCKEditorImages(Request $request)
    {
        $model = new Blog();
        $model->id = $request->input('crud_id', 0);
        $model->exists = true;
        $media = $model->addMediaFromRequest('upload')->toMediaCollection('ck-media');

        return response()->json([
            'filename' => $media->file_name,
            'uploaded' => 1,
            'url' => $media->getUrl()
        ], 201);
    }

    /**
     * Toggles the publication status of a blog.
     *
     * @param Blog $blog
     * @return JsonResponse
     */
    public function published(Blog $blog): JsonResponse
    {
        $blog->update([
            'is_publish' => !$blog->is_publish
        ]);

        return response()->json(['message' => 'Blog updated successfully.'], 200);
    }
}
