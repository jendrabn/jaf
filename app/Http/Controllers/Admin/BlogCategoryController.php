<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\BlogCategoryDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryRequest;
use App\Models\BlogCategory;
use App\Traits\MediaUploadingTrait;
use Illuminate\Http\JsonResponse;

class BlogCategoryController extends Controller
{
    use MediaUploadingTrait;

    /**
     * Display a listing of the blog categories.
     *
     * @param BlogCategoryDataTable $dataTable
     * @return mixed
     */
    public function index(BlogCategoryDataTable $dataTable): mixed
    {
        return $dataTable->render('admin.blogCategories.index');
    }

    /**
     * Store a newly created blog category in the database.
     *
     * @param BlogCategoryRequest $request
     * @return JsonResponse
     */
    public function store(BlogCategoryRequest $request): JsonResponse
    {
        BlogCategory::create($request->validated());

        return response()->json(['message' => 'Blog Category created successfully.'], 200);
    }

    /**
     * Updates an existing blog category in the database.
     *
     * @param BlogCategoryRequest $request
     * @param BlogCategory $blogCategory
     * @return JsonResponse
     */
    public function update(BlogCategoryRequest $request, BlogCategory $blogCategory): JsonResponse
    {
        $blogCategory->update($request->validated());

        return response()->json(['message' => 'Blog Category updated successfully.'], 200);
    }

    /**
     * Deletes a specified blog category from the database.
     *
     * @param BlogCategory $blogCategory
     * @return JsonResponse
     */
    public function destroy(BlogCategory $blogCategory): JsonResponse
    {
        $blogCategory->delete();

        return response()->json(['message' => 'Blog Category deleted successfully.'], 200);
    }

    /**
     * Deletes multiple blog categories from the database.
     *
     * @param BlogCategoryRequest $request
     * @return JsonResponse
     */
    public function massDestroy(BlogCategoryRequest $request): JsonResponse
    {
        BlogCategory::whereIn('id', $request->validated('ids'))->delete();

        return response()->json(['message' => 'Blog Categories deleted successfully.']);
    }
}
