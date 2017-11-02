<?php

namespace Modules\Category\Models;

use Kalnoy\Nestedset\NodeTrait;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Category\Translations\CategoryTranslation;
use Modules\Content\Traits\SyncTranslations;

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

    /** --------------- Accessors --------------- */

    public function getChainedNameAttribute() : string
    {
        $categories = static::with('ancestors')->ancestorsAndSelf($this->id);
        $name = '';

        foreach ($categories as $category) {
            $name .= $category->name . ' -> ';
        }

        $name = substr($name, 0, -3); // Remove last arrow

        return $name;
    }
}