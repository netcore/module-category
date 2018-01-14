<?php

namespace Modules\Category\Models;

use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Kalnoy\Nestedset\NodeTrait;
use Dimsav\Translatable\Translatable;

use Modules\Admin\Traits\BootStapler;
use Modules\Admin\Traits\StaplerAndTranslatable;
use Modules\Admin\Traits\SyncTranslations;
use Modules\Category\Translations\CategoryTranslation;

/**
 * Modules\Category\Models\Category
 *
 * @property int $id
 * @property int $category_group_id
 * @property int $_lft
 * @property int $_rgt
 * @property int|null $parent_id
 * @property string|null $icon
 * @property string|null $file_icon_file_name
 * @property int|null $file_icon_file_size
 * @property string|null $file_icon_content_type
 * @property string|null $file_icon_updated_at
 * @property int $items_count
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Kalnoy\Nestedset\Collection|\Modules\Category\Models\Category[] $children
 * @property-read array $breadcrumb_links
 * @property-read string $chained_name
 * @property-read string|null $file_icon_link
 * @property-read \Modules\Category\Models\CategoryGroup $group
 * @property-read \Modules\Category\Models\Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Category\Translations\CategoryTranslation[] $translations
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category d()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category listsTranslations($translationField)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category notTranslatedIn($locale = null)
 * @method static \Illuminate\Database\Query\Builder|\Modules\Category\Models\Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category orWhereTranslation($key, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category orWhereTranslationLike($key, $value, $locale = null)
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category translated()
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category translatedIn($locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereCategoryGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereFileIconContentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereFileIconFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereFileIconFileSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereFileIconUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereItemsCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereLft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereRgt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereTranslation($key, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereTranslationLike($key, $value, $locale = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\Category withTranslation()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Category\Models\Category withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\Modules\Category\Models\Category withoutTrashed()
 * @mixin \Eloquent
 */
class Category extends Model implements StaplerableInterface
{
    use NodeTrait,
        SoftDeletes,
        SyncTranslations,
        StaplerAndTranslatable,
        BootStapler;

    /**
     * Stapler and Translatable traits conflict with each other
     * That's why we have created custom trait to resolve this conflict
     */
    use Translatable {
        StaplerAndTranslatable::getAttribute insteadof Translatable;
        StaplerAndTranslatable::setAttribute insteadof Translatable;
    }

    use EloquentTrait {
        StaplerAndTranslatable::getAttribute insteadof EloquentTrait;
        StaplerAndTranslatable::setAttribute insteadof EloquentTrait;
        BootStapler::boot insteadof EloquentTrait;
    }

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
        'file_icon',
        'items_count',
    ];

    /**
     * Attributes that are translated.
     *
     * @var array
     */
    public $translatedAttributes = [
        'name',
        'full_slug',
        'slug',
    ];

    /**
     * Translatable translation model.
     *
     * @var string
     */
    public $translationModel = CategoryTranslation::class;

    /**
     * Stapler configuration for file icon.
     *
     * @var array
     */
    protected $staplerConfig = [
        'file_icon' => [
            'url' => '/uploads/:class/:attachment/:id_partition/:style/:filename',
        ],
    ];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = [
        'translations',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'file_icon_link',
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

    /**
     * Get the link to file icon.
     *
     * @return string|null
     */
    public function getFileIconLinkAttribute(): ?string
    {
        return $this->file_icon_content_type ? url($this->file_icon->url()) : null;
    }

    /** --------------- Relations --------------- */

    /**
     * Category belongs to category group.
     *
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(CategoryGroup::class);
    }
}