<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogTag;
use Illuminate\Http\JsonResponse;
use App\DataTables\BlogTagDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogTagRequest;

class BlogTagController extends Controller
{
    /**
     * Display a listing of the blog tags.
     *
     * @param BlogTagDataTable $dataTable
     * @return mixed
     */
    public function index(BlogTagDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.blogTags.index');
    }

    /**
     * Store a newly created BlogTag resource in storage.
     *
     * @param BlogTagRequest $request
     * @return JsonResponse
     */
    public function store(BlogTagRequest $request): JsonResponse
    {
        BlogTag::create($request->validated());

        return response()->json(['message' => 'Blog Category created successfully.'], 200);
    }

    /**
     * Update a BlogTag resource in storage.
     *
     * @param BlogTagRequest $request
     * @param BlogTag $blogTag
     * @return JsonResponse
     */
    public function update(BlogTagRequest $request, BlogTag $blogTag): JsonResponse
    {
        $blogTag->update($request->validated());

        return response()->json(['message' => 'Blog Tag updated successfully.'], 200);
    }

    /**
     * Deletes a BlogTag resource from storage.
     *
     * @param BlogTag $blogTag
     * @return JsonResponse
     */
    public function destroy(BlogTag $blogTag): JsonResponse
    {
        $blogTag->delete();

        return response()->json(['message' => 'Blog Tag deleted successfully.'], 200);
    }

    /**
     * Deletes multiple BlogTag resources from storage.
     *
     * @param BlogTagRequest $request
     * @return JsonResponse
     */
    public function massDestroy(BlogTagRequest $request): JsonResponse
    {
        BlogTag::whereIn('id', $request->validated('ids'))->delete();

        return response()->json(['message' => 'Blog Tag deleted successfully.']);
    }
}
