<?php

namespace Modules\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;

class CategoryIcon extends Model implements StaplerableInterface
{
    use EloquentTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'netcore_category__category_icons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'icon',
    ];

    /**
     * Stapler configuration.
     *
     * @var array
     */
    protected $staplerConfig = [
        'file' => [
            'url' => '/uploads/category_icons/:id_partition/:filename',
        ],
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot(): void
    {
        parent::boot();
        static::bootStapler();

        // Modify file name before saving.
        static::saving(function ($model) {
            foreach ($model->staplerConfig as $name => $config) {
                if ($model->isDirty($name . '_file_name') && !is_null($model->{$name . '_file_name'})) {
                    $pathInfo = pathinfo($model->{$name}->originalFileName());

                    if (isset($pathInfo['extension'])) {
                        $newFilename = md5(time()) . '.' . $pathInfo['extension'];
                        $model->{$name}->instanceWrite('file_name', $newFilename);
                    }
                }
            }
        });
    }
}