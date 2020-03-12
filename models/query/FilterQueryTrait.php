<?php

namespace panix\mod\shop\models\query;

use panix\mod\shop\models\Currency;
use panix\mod\shop\models\Product;
use yii\db\Exception;

trait FilterQueryTrait
{
    public function aggregatePrice($function = 'MIN')
    {
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();
        $this->select(['*', "{$function}(CASE WHEN ({$tableName}.`currency_id`)
                    THEN
                        ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`))
                    ELSE
                        {$tableName}.`price`
                END) AS aggregation_price"]);

        $this->orderBy(["aggregation_price" => ($function === 'MIN') ? SORT_ASC : SORT_DESC]);
        $this->distinct(false);
        $this->limit(1);
        //echo $this->createCommand()->rawSql;die;

        //$result = \Yii::$app->db->cache(function ($db) {
        $result = $this->asArray()->one();
        // }, 3600);


        if ($result) {
            return $result['aggregation_price'];
        }
        return null;
    }

    /**
     * Filter products by price
     * @param $value int
     * @param $operator string '=', '>=', '<='
     * @throws Exception
     * @return $this
     */
    public function applyPrice($value, $operator = '=')
    {
        if (!in_array($operator, ['=', '>=', '<='])) {
            throw new Exception('error operator in '.__FUNCTION__);
        }
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();
        if ($value) {
            $this->andWhere("CASE WHEN {$tableName}.`currency_id` IS NOT NULL THEN
            {$tableName}.`price` {$operator} ({$value} / (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`))
        ELSE
        	{$tableName}.`price` {$operator} {$value}
        END");
        }
        return $this;
    }


    public function aggregatePriceSelect($order = SORT_ASC)
    {
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();

        $this->select([$tableName . '.*', "(CASE WHEN ({$tableName}.`currency_id`)
                    THEN
                        ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`))
                    ELSE
                        {$tableName}.`price`
                END) AS aggregation_price"]);

        $this->orderBy(["aggregation_price" => $order]);

        return $this;
    }
}