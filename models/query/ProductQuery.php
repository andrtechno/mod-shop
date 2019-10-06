<?php

namespace panix\mod\shop\models\query;

use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;
use panix\engine\traits\query\TranslateQueryTrait;
use panix\mod\shop\models\traits\EavQueryTrait;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductCategoryRef;

class ProductQuery extends ActiveQuery
{

    use DefaultQueryTrait, EavQueryTrait, TranslateQueryTrait, FilterQueryTrait;

    /**
     * Product by category
     *
     * @return $this
     */
    public function category()
    {
        $this->joinWith(['category']);
        return $this;
    }


    /**
     * @param $manufacturers array|int
     * @return $this
     */
    public function applyManufacturers($manufacturers)
    {
        if (!is_array($manufacturers))
            $manufacturers = [$manufacturers];


        if (empty($manufacturers))
            return $this;

        sort($manufacturers);


        $this->andWhere(['manufacturer_id' => $manufacturers]);
        return $this;
    }


    /**
     * @param $categories array|int|object
     * @return $this
     */
    public function applyCategories($categories)
    {
        if ($categories instanceof Category)
            $categories = [$categories->id];
        else {
            if (!is_array($categories))
                $categories = [$categories];
        }
        //  $tableName = ($this->modelClass)->tableName();
        $this->leftJoin(ProductCategoryRef::tableName(), ProductCategoryRef::tableName() . '.`product`=' . $this->modelClass::tableName() . '.`id`');
        $this->andWhere([ProductCategoryRef::tableName() . '.`category`' => $categories]);

        return $this;
    }


    /**
     * Product by manufacturer
     *
     * @return $this
     */
    public function manufacturer()
    {
        $this->joinWith(['manufacturer']);
        return $this;
    }


    /**
     * @param null $q
     * @return $this
     */
    public function applySearch($q = null)
    {
        if ($q) {
            $this->joinWith(['translations as translate']);
            $this->andWhere(['LIKE', Product::tableName() . '.sku', $q]);
            $this->orWhere(['LIKE', 'translate.name', $q]);
        }
        return $this;
    }


}
