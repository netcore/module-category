<?php

namespace Modules\Category\Translations;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Modules\Category\Models\Category;

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
}