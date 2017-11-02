<?php

namespace Modules\Category\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Category\Models\Category;
use Modules\Category\Repositories\CategoryRepository;
use Netcore\Translator\Helpers\TransHelper;
use Netcore\Translator\Models\Language;

class RegenerateCategoryFullSlugs implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Category|null
     */
    protected $category;

    /**
     * @var Language|null
     */
    protected $language;

    /**
     * @var CategoryRepository
     */
    protected $repo;

    /**
     * RegenerateCategoryFullSlugs constructor.
     *
     * @param null|Category $category
     * @param null|Language $language
     */
    public function __construct($category = null, $language = null)
    {
        if ($category instanceof Category) {
            $this->category = $category;
        }

        if ($language instanceof Language) {
            $this->language = $language;
        }

        $this->repo = app(CategoryRepository::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        // Update slug for specific category
        if ($this->category) {
            $this->setCategoryFullSlug($this->category);
            return;
        }

        // Regenerate full slug for all categories
        $categories = Category::root()->get();

        $categories->each(function (Category $category) {
            $this->setCategoryFullSlug($category);
        });
    }

    /**
     * Generate full slug for category
     *
     * @param Category $category
     * @return void
     */
    private function setCategoryFullSlug(Category $category): void
    {
        static $languages;

        if (! $languages) {
            $languages = $this->language ? [$this->language] : TransHelper::getAllLanguages();
        }

        foreach ($languages as $language) {
            $category->translations()->where('locale', $language->iso_code)->update([
                'full_slug' => $this->getSlugForCategory($category->id, $language->iso_code),
            ]);
        }

        foreach ($category->descendants()->get() as $descendant) {
            foreach ($languages as $language) {
                $descendant->translations()->where('locale', $language->iso_code)->update([
                    'full_slug' => $this->getSlugForCategory($descendant->id, $language->iso_code),
                ]);
            }
        }
    }

    /**
     * Build full slug for category
     *
     * @param int $categoryId
     * @param string $locale
     * @return string
     */
    private function getSlugForCategory(int $categoryId, string $locale): string
    {
        $reversed = array_reverse(
            $this->getSlugParts($categoryId, $locale)
        );

        return implode('/', $reversed);
    }

    /**
     * Get slug parts recursively without querying database
     *
     * @param int $categoryId
     * @param string $locale
     * @return array
     */
    private function getSlugParts(int $categoryId, string $locale): array
    {
        $category = $this->repo->getCategoriesPlain()->where('id', $categoryId)->first();
        $slugParts = [];

        if (!$category) {
            // Try to fetch from DB
            $category = Category::find($categoryId);

            if (!$category) {
                return [];
            }

            $category = [
                'id'        => $category->id,
                'parent_id' => $category->parent_id,
                'slugs'     => $category->translations->pluck('slug', 'locale')->toArray(),
            ];
        }

        $slugParts[] = array_get($category, 'slugs.' . $locale);

        if (isset($category['parent_id']) && $category['parent_id']) {
            $slugParts = array_merge($slugParts, $this->getSlugParts($category['parent_id'], $locale));
        }

        return $slugParts;
    }
}
