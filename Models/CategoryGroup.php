<?php

namespace Modules\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Category\Icons\IconSetInterface;

/**
 * Modules\Category\Models\CategoryGroup
 *
 * @property int $id
 * @property string $key
 * @property string $title
 * @property int $has_icons
 * @property int $icons_for_only_roots
 * @property string $icons_type
 * @property string|null $icons_presenter_class
 * @property int|null $levels
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Kalnoy\Nestedset\Collection|\Modules\Category\Models\Category[] $categories
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\CategoryGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\CategoryGroup whereHasIcons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\CategoryGroup whereIconsForOnlyRoots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\CategoryGroup whereIconsPresenterClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\CategoryGroup whereIconsType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\CategoryGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\CategoryGroup whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\CategoryGroup whereLevels($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\CategoryGroup whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Modules\Category\Models\CategoryGroup whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CategoryGroup extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'netcore_category__category_groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'title',
        'has_icons',
        'icons_only_for_roots',
        'icons_type',
        'icons_presenter_class',
        'levels',
    ];

    /** -------------------- Relations -------------------- */

    /**
     * Category group has many categories.
     *
     * @return HasMany
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /** -------------------- Helpers -------------------- */

    /**
     * Determine if icon presenter exists for current group.
     *
     * @return bool
     */
    public function hasPresenter(): bool
    {
        static $hasPresenter;

        if (is_bool($hasPresenter)) {
            return $hasPresenter;
        }

        if ($this->icons_type != 'select2') {
            return false;
        }

        $className = $this->icons_presenter_class;
        $hasPresenter = class_exists($className) && app($className) instanceof IconSetInterface;

        return $hasPresenter;
    }

    /**
     * Get the icons presenter instance.
     *
     * @return IconSetInterface
     */
    public function getPresenter(): ?IconSetInterface
    {
        static $presenter;

        if ($presenter instanceof IconSetInterface) {
            return $presenter;
        }

        return $presenter = $this->hasPresenter() ? app($this->icons_presenter_class) : null;
    }
}