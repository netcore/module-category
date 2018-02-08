<?php

namespace Modules\Category\Tests;

use Modules\Category\Models\Category;
use Modules\Category\Models\CategoryGroup;
use Netcore\Translator\Helpers\TransHelper;
use PDOException;
use Tests\TestCase;

class BaseTest extends TestCase
{
    /**
     * Test category group key for unique rule.
     *
     * @return void
     * @throws \Exception
     */
    public function testCategoryGroupDuplicate(): void
    {
        // We should expect integrity constraint violation when creating category group with same key.
        $this->expectException(PDOException::class);

        $key = 'test_category';
        $title = 'Test category';

        CategoryGroup::where('key', $key)->delete();

        // @throws: PDO exception.
        for ($i = 0; $i <= 1; $i++) {
            CategoryGroup::create([
                'key'   => $key,
                'title' => $title,
            ]);
        }
    }

    /**
     * Test sluggable logic.
     *
     * @return void
     * @throws \Exception
     */
    public function testCategorySlugGeneration(): void
    {
        $group = CategoryGroup::where('key', 'test_category')->firstOrFail();
        $languages = TransHelper::getAllLanguages();

        $group->categories()->forceDelete();

        // Create categories.
        $traverse = function (array $categories, $parentCategory = null) use (&$traverse, $group, $languages) {
            foreach ($categories as $category) {
                $categoryInstance = $group->categories()->create([]);

                // It's very important to append category to the parent before we are storing translations.
                // Otherwise full_slug will be incorrect!
                if ($parentCategory && $parentCategory instanceof Category) {
                    $parentCategory->appendNode($categoryInstance);
                }

                $categoryInstance->storeTranslations([
                    'en' => [
                        'name' => $category['name'],
                    ],
                ]);

                if (isset($category['children']) && is_array($category['children'])) {
                    $traverse($category['children'], $categoryInstance);
                }
            }
        };

        $traverse($this->getTestableCategories());

        app()->setLocale('en');

        // Check slugs.
        $testTraverse = function (array $categories) use (&$testTraverse, $group) {
            foreach ($categories as $category) {
                $existingCategory = $group->categories()->whereTranslation('name', $category['name'])->first();

                $this->assertEquals($category['expected_slug'], $existingCategory->slug);
                $this->assertEquals($category['expected_slug_full'], $existingCategory->full_slug);

                if (isset($category['children']) && is_array($category['children'])) {
                    $testTraverse($category['children']);
                }
            }
        };

        $testTraverse($this->getTestableCategories());
    }

    /**
     * Get seedable and testable data for category items.
     *
     * @return array
     */
    private function getTestableCategories(): array
    {
        return [
            [
                'name'               => 'Root 1',
                'expected_slug'      => 'root-1',
                'expected_slug_full' => 'root-1',
                'children'           => [
                    [
                        'name'               => 'Root 1 - Child 1',
                        'expected_slug'      => 'root-1-child-1',
                        'expected_slug_full' => 'root-1/root-1-child-1',
                    ],
                    [
                        'name'               => 'Root 1 - Child 2',
                        'expected_slug'      => 'root-1-child-2',
                        'expected_slug_full' => 'root-1/root-1-child-2',
                    ],
                    [
                        'name'               => 'Root 1 - Child 3',
                        'expected_slug'      => 'root-1-child-3',
                        'expected_slug_full' => 'root-1/root-1-child-3',
                    ],
                ],
            ],
            [
                'name'               => 'Root 2',
                'expected_slug'      => 'root-2',
                'expected_slug_full' => 'root-2',
                'children'           => [
                    [
                        'name'               => 'Root 2 - Child 1',
                        'expected_slug'      => 'root-2-child-1',
                        'expected_slug_full' => 'root-2/root-2-child-1',
                    ],
                    [
                        'name'               => 'Root 2 - Child 2',
                        'expected_slug'      => 'root-2-child-2',
                        'expected_slug_full' => 'root-2/root-2-child-2',
                    ],
                    [
                        'name'               => 'Root 2 - Child 3',
                        'expected_slug'      => 'root-2-child-3',
                        'expected_slug_full' => 'root-2/root-2-child-3',
                    ],
                ],
            ],
        ];
    }
}