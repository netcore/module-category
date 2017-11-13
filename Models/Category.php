<?php

namespace Modules\Category\Models;

use Kalnoy\Nestedset\NodeTrait;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Category\Translations\CategoryTranslation;
use Modules\Classified\Models\Parameter;
use Modules\Admin\Traits\SyncTranslations;

class Category extends Model
{
    use NodeTrait, SoftDeletes, Translatable, SyncTranslations;

    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'netcore_category__categories';

    /**
     * Mass assignable fields
     *
     * @var array
     */
    protected $fillable = [
        'icon',
        'classified_count'
    ];

    /**
     * Enable timestamps
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Attributes that are translated
     *
     * @var array
     */
    public $translatedAttributes = [
        'name',
        'full_slug',
        'slug',
    ];

    /**
     * Translatable translation model
     *
     * @var string
     */
    public $translationModel = CategoryTranslation::class;

    /**
     * Eager loaded relations
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|\Illuminate\Support\Collection
     */
    public function parameters()
    {
        if (config('netcore.module-classified.parameters.attach_to_categories')) {
            return $this->belongsToMany(Parameter::class, 'netcore_classified__category_parameter');
        }

        return collect([]);
    }

    /** --------------- Accessors --------------- */

    public function getChainedNameAttribute(): string
    {
        $categories = static::with('ancestors')->ancestorsAndSelf($this->id);
        $name = '';

        foreach ($categories as $category) {
            $name .= $category->name . ' -> ';
        }

        $name = substr($name, 0, -3); // Remove last arrow

        return $name;
    }

    /**
     * Get breadcrumb links
     *
     * @return array
     */
    public function getBreadcrumbLinksAttribute(): array
    {
        $categories = static::with('ancestors')->ancestorsAndSelf($this->id);
        $breadcrumbs = [];

        foreach ($categories as $category) {
            $breadcrumbs[url($category->full_slug)] = $category->name;
        }

        return $breadcrumbs;
    }

}