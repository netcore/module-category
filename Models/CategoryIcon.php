<?php

namespace Modules\Category\Models;

use Illuminate\Database\Eloquent\Model;
use Codesleeve\Stapler\ORM\EloquentTrait;
use Codesleeve\Stapler\ORM\StaplerableInterface;

class CategoryIcon extends Model implements StaplerableInterface
{
    use EloquentTrait;

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
        static::saving(function (CategoryIcon $categoryIcon) {
            if ($categoryIcon->isDirty('icon_file_name') && !is_null($categoryIcon->icon_file_name)) {
                $pathInfo = pathinfo(
                    $categoryIcon->icon->originalFileName()
                );

                if ($pathInfo && isset($pathInfo['extension'])) {
                    $newFilename = md5(time()) . '.' . $pathInfo['extension'];
                    $categoryIcon->icon->instanceWrite('file_name', $newFilename);
                }
            }
        });
    }

    /**
     * CategoryIcon constructor.
     *
     * @param array $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->hasAttachedFile('icon', [
            'url' => '/uploads/category_icons/:id_partition/:filename',
        ]);

        parent::__construct($attributes);
    }

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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Overridden to prevent attempts to persist attachment attributes directly.
     *
     * Reason this is required: Laravel 5.5 changed the getDirty() behavior.
     *
     * {@inheritdoc}
     */
    protected function originalIsEquivalent($key, $current)
    {
        if (array_key_exists($key, $this->attachedFiles)) {
            return true;
        }

        return parent::originalIsEquivalent($key, $current);
    }
}