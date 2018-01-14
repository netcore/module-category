<?php

namespace Modules\Category\Http\Controllers\Admin;

use Cviebrock\EloquentSluggable\Services\SlugService;
use Exception;
use Illuminate\Cache\RedisStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

use Illuminate\Support\Collection;
use Modules\Category\Icons\IconSet;
use Modules\Category\Jobs\RegenerateCategoryFullSlugs;
use Modules\Category\Models\Category;
use Modules\Category\Http\Requests\CategoryRequest;

use Modules\Category\Models\CategoryGroup;
use Modules\Category\Traits\ControllerHelpersTrait;
use Modules\Category\Translations\CategoryTranslation;
use Netcore\Translator\Helpers\TransHelper;

class CategoryController extends Controller
{
    use ControllerHelpersTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
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
     * Store category.
     *
     * @param CategoryGroup $categoryGroup
     * @param CategoryRequest $request
     * @return JsonResponse
     */
    public function store(CategoryGroup $categoryGroup, CategoryRequest $request)
    {
        $category = $categoryGroup->categories()->create(
            $request->only(['icon', 'file_icon'])
        );

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
     * Update category.
     *
     * @param CategoryRequest $request
     * @param CategoryGroup $categoryGroup
     * @param Category $category
     * @return JsonResponse
     */
    public function update(CategoryRequest $request, CategoryGroup $categoryGroup, Category $category)
    {
        $category = $categoryGroup->categories()->findOrFail(
            $category->id
        );

        $category->update(
            $request->only(['icon', 'file_icon'])
        );

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
     * @param CategoryGroup $categoryGroup
     * @param Category $category
     * @return JsonResponse
     */
    public function destroy(CategoryGroup $categoryGroup, Category $category)
    {
        $categories = $categoryGroup->categories()->descendantsAndSelf($category->id);

        foreach ($categories as $item) {
            $item->delete();
        };

        $this->clearCache();

        return response()->json([
            'state' => 'success',
        ]);
    }

    /**
     * Rebuild categories tree
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOrder(Request $request)
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

        $categories = $categories->map(function (Category $category) use ($isTreeOpened, $suffixHelper, $language) {
            $categoryName = trans_model($category, $language, 'name');

            if ($suffixHelper && function_exists($suffixHelper)) {
                $categoryName .= $suffixHelper($category);
            }

            return [
                'id'           => $category->id,
                'parent'       => $category->parent_id ?: '#',
                'text'         => $categoryName,
                'icon'         => $category->icon,
                'file_icon'    => $category->file_icon_link,
                'li_attr'      => [],
                'a_attr'       => [],
                'translations' => $category->translations,
                'state'        => [
                    'opened'   => $isTreeOpened,
                    'disabled' => false,
                    'selected' => false,
                ],
            ];
        });

        return $categories;
    }
}
