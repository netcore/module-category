<?php

namespace Modules\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Kalnoy\Nestedset\NodeTrait;
use Dimsav\Translatable\Translatable;

use Modules\Admin\Traits\SyncTranslations;
use Modules\Category\Translations\CategoryTranslation;

class Category extends Model
{
    use NodeTrait, SoftDeletes, Translatable, SyncTranslations;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'netcore_category__categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'icon',
        'items_count',
    ];

    /**
     * Attributes that are translated.
     *
     * @var array
     */
    public $translatedAttributes = [
        'name',
        'slug',
        'full_slug',
    ];

    /**
     * Translatable translation model.
     *
     * @var string
     */
    public $translationModel = CategoryTranslation::class;

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'icons',
        'translations',
    ];

    /** --------------- Accessors --------------- */

    /**
     * Get chained category name.
     * Note: To use this, you should eager load ancestors (->with('ancestors'))
     *
     * @return string
     */
    public function getChainedNameAttribute(): string
    {
        $categories = $this->breadcrumbLinks;
        $names = [];

        foreach ($categories as $url => $categoryName) {
            $names[] = $categoryName;
        }

        return implode(' -> ', $names);
    }

    /**
     * Get breadcrumb links
     * Note: To use this, you should eager load ancestors (->with('ancestors'))
     *
     * @return array
     */
    public function getBreadcrumbLinksAttribute(): array
    {
        $breadcrumbs = [];

        foreach ($this->ancestors as $ancestor) {
            $breadcrumbs[url($ancestor->full_slug)] = $ancestor->name;
        }

        $breadcrumbs[url($this->full_slug)] = $this->name;

        return $breadcrumbs;
    }

    /** --------------- Relations --------------- */

    /**
     * Category belongs to category group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(CategoryGroup::class);
    }

    /**
     * Category has many icons.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function icons(): HasMany
    {
        return $this->hasMany(CategoryIcon::class);
    }
}