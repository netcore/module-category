<?php

namespace Modules\Category\Translations;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Modules\Category\Models\Category;
use Netcore\Translator\Models\Language;

/**
 * Modules\Category\Translations\CategoryTranslation
 *
 * @property int $id
 * @property int $category_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property string|null $full_slug
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Modules\Category\Models\Category $category
 * @property-read \Netcore\Translator\Models\Language $language
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Translations\CategoryTranslation findSimilarSlugs($attribute, $config, $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Translations\CategoryTranslation whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Translations\CategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Translations\CategoryTranslation whereFullSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Translations\CategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Translations\CategoryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Translations\CategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Translations\CategoryTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Translations\CategoryTranslation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CategoryTranslation extends Model
{
    use Sluggable, SluggableScopeHelpers;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'netcore_category__category_translations';

    /**
     * Mass assignable fields
     *
     * @var array
     */
    protected $fillable = [
        'locale',
        'name',
        'slug',
        'full_slug',
    ];

    /**
     * Enable timestamps
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Return the sluggable configuration for this model
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    /** --------------- Relations --------------- */

    /**
     * Translation belongs to category
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Category translation belongs to language
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function language()
    {
        return $this->belongsTo(Language::class, 'locale', 'iso_code');
    }

    /** --------------- Helpers --------------- */

    /**
     * Get full slug of the category
     *
     * @return string
     */
    public function getFullSlug() : string
    {
        $categories = Category::with('ancestors')->ancestorsAndSelf($this->category->id);

        $slug = "";

        foreach ($categories as $category) {
            $translation = $category->translations->where('locale', $this->locale)->first();
            $slug .= $translation->slug . '/';
        }

        $slug = substr($slug, 0, -1);

        return $slug;
    }
}