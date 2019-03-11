<?php

namespace panix\mod\shop\models\query;

use yii\db\ActiveQuery;
use panix\engine\traits\DefaultQueryTrait;
use panix\mod\shop\models\traits\EavQueryTrait;
use panix\mod\shop\models\Category;

class ProductQuery extends ActiveQuery
{

    use DefaultQueryTrait, EavQueryTrait;

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
            $manufacturers = array($manufacturers);

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
        $this->leftJoin('{{%shop__product_category_ref}}', '{{%shop__product_category_ref}}.`product`=' . $this->modelClass::tableName() . '.`id`');
        $this->andWhere(['{{%shop__product_category_ref}}.`category`' => $categories]);

        return $this;
    }


    /**
     * Filter products by min_price
     * @param $value float
     * @return $this
     */
    public function applyMinPrice($value)
    {
        if ($value) {
            //  $this->andWhere(['>=', 'price', (int)$value]);

            $this->andWhere('CASE WHEN ({{%shop__product}}.`currency_id`) THEN
            ({{%shop__product}}.`price` * (SELECT rate FROM {{%shop__currency}} `currency` WHERE `currency`.`id`={{%shop__product}}.`currency_id`)) >= ' . (int)$value . '
        ELSE
        	{{%shop__product}}.price >= ' . (int)$value . '
        END');

        }
        return $this;
    }

    /**
     * Filter products by max_price
     * @param $value float
     * @return $this
     */
    public function applyMaxPrice($value)
    {
        if ($value) {
            //$this->andWhere(['<=', 'price', (int)$value]);

            $this->andWhere('CASE WHEN ({{%shop__product}}.`currency_id`) THEN
            ({{%shop__product}}.`price` * (SELECT rate FROM {{%shop__currency}} `currency` WHERE `currency`.`id`={{%shop__product}}.`currency_id`)) <= ' . (int)$value . '
        ELSE
        	{{%shop__product}}.price <= ' . (int)$value . '
        END');
        }
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

}
