<?php

namespace Modules\Category\Observers;

use Modules\Category\Jobs\RegenerateCategoryFullSlugs;
use Modules\Category\Translations\CategoryTranslation;
use DB;

class CategoryTranslationObserver
{
    /**
     * Listen to the CategoryTranslation created event.
     *
     * @param CategoryTranslation $categoryTranslation
     */
    public function created(CategoryTranslation $categoryTranslation)
    {
        DB::table('netcore_category__category_translations')->where('id', $categoryTranslation->id)->update([
            'full_slug' => $categoryTranslation->getFullSlug()
        ]);
    }

    /**
     * Listen to the CategoryTranslation updated event.
     *
     * @param CategoryTranslation $categoryTranslation
     */
    public function updated(CategoryTranslation $categoryTranslation)
    {
        DB::table('netcore_category__category_translations')->where('id', $categoryTranslation->id)->update([
            'full_slug' => $categoryTranslation->getFullSlug()
        ]);

        dispatch(
            new RegenerateCategoryFullSlugs(
                $categoryTranslation->category,
                $categoryTranslation->language
            )
        );
    }
}