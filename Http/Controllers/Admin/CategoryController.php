<?php

namespace Modules\Category\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Netcore\Translator\Helpers\TransHelper;
use Modules\Category\Models\Category;
use Modules\Category\Http\Requests\CategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Support\Collection
     */
    public function index()
    {
        if (request()->wantsJson()) {
            return $this->getCategoriesTreeJson();
        }

        $languages = TransHelper::getAllLanguages();

        return view('category::index', compact('languages'));
    }

    /**
     * Store category
     *
     * @param CategoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create(
            $request->only('icon')
        );

        $category->storeTranslations(
            $request->input('translations')
        );

        if ($request->has('parent') && $parent = Category::find($request->input('parent'))) {
            $parent->appendNode($category);
        }

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Update category
     *
     * @param CategoryRequest $request
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update([
            'icon' => $request->input('icon'),
        ]);

        $category->updateTranslations(
            $request->input('translations')
        );

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Delete category and it's descendants
     *
     * @param Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Category $category)
    {
        $categories = Category::descendantsAndSelf($category->id);

        foreach ($categories as $item) {
            $item->delete();
        };

        return response()->json([
            'state' => 'success'
        ]);
    }

    /**
     * Rebuild categories tree
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        Category::rebuildTree(
            $request->all()
        );

        return response()->json([
            'state' => 'success'
        ]);
    }

    /**
     * Get data for JsTree
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getCategoriesTreeJson()
    {
        $categories = Category::defaultOrder()->get()->map(function (Category $category) {
            return [
                'id'           => $category->id,
                'parent'       => $category->parent_id ?: '#',
                'text'         => trans_model($category, TransHelper::getLanguage(), 'name'),
                'icon'         => 'fa fa-folder-o',
                'li_attr'      => [],
                'a_attr'       => [],
                'translations' => $category->translations,
                'state'        => [
                    'opened'   => true,
                    'disabled' => false,
                    'selected' => false,
                ],
            ];
        });

        return $categories;
    }
}
