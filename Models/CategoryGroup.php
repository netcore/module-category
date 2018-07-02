<?php

namespace Modules\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Category\Icons\IconSetInterface;

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
        'file_icons',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'file_icons' => 'collection',
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