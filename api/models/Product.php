<?php

namespace panix\mod\shop\api\models;


use panix\engine\api\ApiHelpers;
use panix\engine\CMS;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Product as BaseProduct;
use yii\helpers\Url;
use Yii;

/**
 * Class Product
 * @package api\modules\v1\models
 *
 *
 * GET v1/product?token={token} Список товаров
 * GET,PUT,DELETE v1/product/{id}?token={token} Товар
 *
 * $_GET params "&expand=characteristics,<etc>"
 * $_GET params "&field=id,<etc>"
 */
class Product extends BaseProduct
{

    public $images;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['api_create'] = ['name_ru', 'main_category_id', 'price', 'type_id'];
        $scenarios['api_update'] = ['name_ru', 'main_category_id', 'price'];
        return $scenarios;
    }

    public function getMainCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category'])
            ->via('categorization', function ($query) {
                /** @var Query $query */
                $query->where(['is_main' => 1]);
            });
    }

    public function getCategories()
    {
        return $this->hasMany(Category::class, ['id' => 'category'])->via('categorization');
    }

    public function fields()
    {
        $data = [];
        return [
            'id',
            'type' => function ($model) {
                return ['id' => $model->type_id, 'name' => $model->type->name];
            },
            'url' => function ($model) {
                return ApiHelpers::url("/product/" . $model->slug . '-' . $model->id, true);
            },
            'is_condition',
            'views',
            'video',
            'name',
            'price',
            'sku',
            'discount',
            'main_image' => function ($model) {
                if (file_exists(Yii::getAlias("@uploads/store/product/{$model->id}/{$model->image}"))) {
                    return ApiHelpers::url("/uploads/store/product/{$model->id}/" . $model->image, true);
                } else {
                    return ApiHelpers::url("/uploads/no-image.jpg", true);
                }
            },

            /*'updated_at' => function ($model) {
                if ($model->updated_at) {
                    return [
                        'timestemp' => $model->updated_at,
                        'date' => CMS::date($model->updated_at, false),
                        'datetime' => CMS::date($model->updated_at)
                    ];
                }
            },*/

            'categories' => function ($model) {
                return [
                    'main_category' => $model->mainCategory,
                    'items' => $model->categories
                ];
            },

            'brand' => function ($model) {
                return $model->brand;
            },
            'supplier' => function ($model) {
                return $model->supplier;
            },
            'availability' => function ($model) {
                return [
                    'value' => $model->availability,
                    'title' => self::getAvailabilityItems()[$model->availability]
                ];
            },
            'rating' => function ($model) {
                return [
                    'rating' => $model->rating,
                    'votes' => $model->votes,
                    'score' => $model->ratingScore,
                    'title' => Yii::t('shop/default', 'RATING_SCORE', $model->ratingScore),
                ];
            },
            'created_at' => function ($model) {
                if ($model->created_at) {
                    return [
                        'timestemp' => $model->created_at,
                        'date' => CMS::date($model->created_at, false),
                        'datetime' => CMS::date($model->created_at)
                    ];
                }
            },
            /*'images' => function ($model) {
                $image = [];
                foreach ($model->getImages() as $img) {
                    $image[] = [
                        'id' => $img->id,
                        'is_main' => $img->is_main,
                        'url' => Url::to($img->getUrlToOrigin(), true),
                    ];

                }
                return $image;
            },*/
            'currency' => function ($model) {
                if ($model->currency_id) {
                    return $model->currency_id;
                }
                return 'UAH';
            },
            'reviews' => function ($model) {
                return $model->reviews;
            },
            'characteristics' => function ($model) {
                $attributes = $model->getEavAttributes();
                $data = [];
                $query = Attribute::find()
                    ->where(['IN', 'name', array_keys($attributes)])
                    ->sort()
                    ->all();


                foreach ($query as $attr) {
                    $value = $attr->renderValue($attributes[$attr->name]);
                    $data[] = [
                        'id' => $attr->id,
                        'title' => $attr->title,
                        'value' => $value
                    ];
                }
                return $data;
            },

        ];
    }

    public function extraFields()
    {
        return ['prices', 'characteristics', 'reviews', 'categories', 'brand'];
    }

    public function getCharacteristics()
    {
        $attributes = $this->getEavAttributes();

        $_list = [];

        $query = Attribute::find()
            ->where(['IN', 'name_ru', array_keys($attributes)])
            ->sort()
            ->all();

        foreach ($query as $m) {
            $_list[] = [
                'data' => $m,
                'value' => $m->renderValue($attributes[$m->name])
            ];

        }

        return $_list;
    }

}
