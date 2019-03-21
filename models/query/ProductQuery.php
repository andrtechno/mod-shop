<?php

namespace panix\mod\shop\models\query;

use panix\mod\shop\models\Currency;
use panix\mod\shop\models\Product;
use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;
use panix\engine\traits\query\TranslateQueryTrait;
use panix\mod\shop\models\traits\EavQueryTrait;
use panix\mod\shop\models\Category;

class ProductQuery extends ActiveQuery
{

    use DefaultQueryTrait, EavQueryTrait, TranslateQueryTrait;

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
     * @param $value int
     * @return $this
     */
    public function applyMinPrice($value)
    {
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();
        if ($value) {
            //  $this->andWhere(['>=', 'price', (int)$value]);

            $this->andWhere("CASE WHEN ({$tableName}.`currency_id`) THEN
            ({$tableName}.price * (SELECT rate FROM {$tableNameCur} `currency` WHERE `currency`.`id`={$tableName}.`currency_id`)) >= {$value}
        ELSE
        	{$tableName}.price >= {$value}
        END");

        }
        return $this;
    }

    /**
     * Filter products by max_price
     * @param $value int
     * @return $this
     */
    public function applyMaxPrice($value)
    {
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();
        if ($value) {
            //$this->andWhere(['<=', 'price', (int)$value]);

            $this->andWhere("CASE WHEN ({$tableName}.`currency_id`) THEN
            ({$tableName}.price * (SELECT rate FROM {$tableNameCur} `currency` WHERE `currency`.`id`={$tableName}.`currency_id`)) <= {$value}
        ELSE
        	{$tableName}.price <= {$value}
        END");
        }
        return $this;
    }

    /**
     * Filter products by price
     * @param $value int
     * @return $this
     */
    public function applyPrice($value)
    {
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();
        if ($value) {
            $this->andWhere("CASE WHEN ({$tableName}.`currency_id`) THEN
            ({$tableName}.price * (SELECT rate FROM {$tableNameCur} `currency` WHERE `currency`.`id`={$tableName}.`currency_id`)) = {$value}
        ELSE
        	{$tableName}.price = {$value}
        END");
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


    public function aggregatePrice($function = 'MIN')
    {
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();
        $this->addSelect(['*', "{$function}((CASE WHEN ({$tableName}.`currency_id`)
                    THEN
                        ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} `currency` WHERE `currency`.`id`={$tableName}.`currency_id`))
                    ELSE
                        {$tableName}.`price`
                END)) AS aggregation_price"]);


        /*$this->select("{$function}((CASE WHEN ({$tableName}.`currency_id`)
                    THEN
                        ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} `currency` WHERE `currency`.`id`={$tableName}.`currency_id`))
                    ELSE
                        {$tableName}.`price`
                END)) AS aggregation_price");*/

        $this->addOrderBy(["aggregation_price" => ($function === 'MIN') ? SORT_ASC : SORT_DESC]);
        $this->distinct(false);
        $this->limit(1);


        $result = \Yii::$app->db->cache(function ($db) {
            return $this->asArray()->one();
        }, 3600);


        if ($result) {
            return $result['aggregation_price'];
        }
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

    public function aggregatePriceSelect($order = SORT_ASC)
    {
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();
        $this->addSelect(['*', "(CASE WHEN ({$tableName}.`currency_id`)
                    THEN
                        ({$tableName}.price * (SELECT rate FROM {$tableNameCur} `currency` WHERE `currency`.`id`={$tableName}.`currency_id`))
                    ELSE
                        {$tableName}.price
                END) AS aggregation_price"]);

        $this->orderBy(["aggregation_price" => $order]);
        return $this;
    }

}
