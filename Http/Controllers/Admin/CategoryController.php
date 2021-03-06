<?php

namespace Modules\Category\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

use Modules\Category\Icons\IconSet;
use Modules\Category\Models\Category;
use Modules\Category\Models\CategoryGroup;
use Modules\Category\Http\Requests\CategoryRequest;
use Modules\Category\Models\CategoryIcon;
use Modules\Category\Traits\ControllerHelpersTrait;
use Modules\Category\Jobs\RegenerateCategoryFullSlugs;

use Netcore\Translator\Helpers\TransHelper;

class CategoryController extends Controller
{
    use ControllerHelpersTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $categoryGroups = CategoryGroup::all();
        $selectedGroupId = request()->get('group', optional($categoryGroups->first())->id);

        if (!$categoryGroups->pluck('id')->contains($selectedGroupId)) {
            abort(404);
        }

        $categoryGroup = $categoryGroups->where('id', $selectedGroupId)->first();
        $languages = TransHelper::getAllLanguages();

        return view('category::index', compact('categoryGroups', 'categoryGroup', 'languages'));
    }

    /**
     * Store category in the database.
     *
     * @param \Modules\Category\Models\CategoryGroup $categoryGroup
     * @param \Modules\Category\Http\Requests\CategoryRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryGroup $categoryGroup, CategoryRequest $request): JsonResponse
    {
        $category = $categoryGroup->categories()->create(
            $request->only('icon')
        );

        foreach ($request->file('icons', []) as $key => $file) {
            if ($categoryGroup->hasFileIcon($key) && $file) {
                $category->icons()->create([
                    'key'  => $key,
                    'icon' => $file,
                ]);
            }
        }

        $this->modifySlugs($request);

        $category->storeTranslations(
            $request->input('translations')
        );

        if ($request->has('parent_id') && $parent = Category::find($request->input('parent_id'))) {
            $parent->appendNode($category);
        }

        dispatch(
            new RegenerateCategoryFullSlugs($category)
        );

        $this->clearCache();

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Update category in the database.
     *
     * @param \Modules\Category\Http\Requests\CategoryRequest $request
     * @param \Modules\Category\Models\CategoryGroup $categoryGroup
     * @param \Modules\Category\Models\Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryRequest $request, CategoryGroup $categoryGroup, Category $category): JsonResponse
    {
        if ($category->category_group_id !== $categoryGroup->id) {
            abort(404);
        }

        $category->update(
            $request->only('icon')
        );

        foreach ($request->file('icons', []) as $key => $file) {
            if ($categoryGroup->hasFileIcon($key) && $file) {
                $category->icons()->updateOrCreate(
                    ['key' => $key],
                    ['icon' => $file]
                );
            }
        }

        $this->modifySlugs($request);

        $category->updateTranslations(
            $request->input('translations')
        );

        dispatch(
            new RegenerateCategoryFullSlugs($category)
        );

        $this->clearCache();

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Delete category and it's descendants.
     *
     * @param \Modules\Category\Models\CategoryGroup $categoryGroup
     * @param \Modules\Category\Models\Category $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CategoryGroup $categoryGroup, Category $category): JsonResponse
    {
        $categories = $categoryGroup->categories()->descendantsAndSelf(
            $category->id
        );

        foreach ($categories as $item) {
            $item->delete();
        };

        $this->clearCache();

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Rebuild categories tree.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request): JsonResponse
    {
        Category::rebuildTree(
            $request->input('tree')
        );

        $this->clearCache();

        $movedNode = Category::findOrFail(
            $request->input('moved')
        );

        dispatch(
            new RegenerateCategoryFullSlugs($movedNode)
        );

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Get data for JsTree.
     *
     * @param CategoryGroup $categoryGroup
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchCategories(CategoryGroup $categoryGroup)
    {
        $isTreeOpened = config('netcore.module-category.tree.opened_by_default', false);
        $suffixHelper = config('netcore.module-category.tree.name_suffix_helper_function', null);
        $language = TransHelper::getLanguage();

        $categories = $categoryGroup->categories()->defaultOrder()->get();

        $categories = $categories->map(function (Category $category) use (
            $isTreeOpened,
            $suffixHelper,
            $language,
            $categoryGroup
        ) {
            // Name.
            $categoryName = trans_model($category, $language, 'name');

            if ($suffixHelper && function_exists($suffixHelper)) {
                $categoryName .= $suffixHelper($category);
            }

            // Icons.
            $icons = [];

            if ($categoryGroup->has_icons) {
                $icons = $category->icons->mapWithKeys(function (CategoryIcon $categoryIcon) {
                    return [$categoryIcon->key => $categoryIcon->icon->url()];
                });
            }

            return [
                'id'           => $category->id,
                'parent'       => $category->parent_id ?: '#',
                'text'         => $categoryName,
                'icon'         => $category->icon,
                'li_attr'      => [],
                'a_attr'       => [],
                'translations' => $category->translations,
                'state'        => [
                    'opened'   => $isTreeOpened,
                    'disabled' => false,
                    'selected' => false,
                ],
                'icons'        => $icons,
            ];
        });

        return $categories;
    }
}
