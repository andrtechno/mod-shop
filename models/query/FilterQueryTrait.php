<?php

namespace panix\mod\shop\models\query;

use panix\mod\shop\models\Currency;
use panix\mod\shop\models\Product;

trait FilterQueryTrait
{
    public function aggregatePrice($function = 'MIN')
    {
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();
        $this->addSelect([$tableName . '.*', "{$function}(CASE WHEN ({$tableName}.`currency_id`)
                    THEN
                        ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`))
                    ELSE
                        {$tableName}.`price`
                END) AS aggregation_price"]);

        $this->addOrderBy(["aggregation_price" => ($function === 'MIN') ? SORT_ASC : SORT_DESC]);
        $this->distinct(false);
        $this->limit(1);


        $result = \Yii::$app->db->cache(function ($db) {
            return $this->asArray()->one();
        }, 3600);


        if ($result) {
            return $result['aggregation_price'];
        }
        return null;
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

            $this->andWhere("CASE WHEN ({$tableName}.`currency_id` != NULL) THEN
            ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`)) >= {$value}
        ELSE
        	{$tableName}.`price` >= {$value}
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

            $this->andWhere("CASE WHEN ({$tableName}.`currency_id` != NULL) THEN
            ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`)) <= {$value}
        ELSE
        	{$tableName}.`price` <= {$value}
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
            $this->andWhere("CASE WHEN ({$tableName}.`currency_id` != NULL) THEN
            ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`)) = {$value}
        ELSE
        	{$tableName}.`price` = {$value}
        END");
        }
        return $this;
    }


    public function aggregatePriceSelect($order = SORT_ASC)
    {
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();

        $this->addSelect([$tableName.'.*',"(CASE WHEN ({$tableName}.`currency_id`)
                    THEN
                        ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`))
                    ELSE
                        {$tableName}.`price`
                END) AS aggregation_price"]);

        $this->orderBy(["aggregation_price" => $order]);

        return $this;
    }
}