<?php

namespace panix\mod\shop\models\query;

use Yii;
use panix\mod\shop\models\Currency;
use panix\mod\shop\models\Product;
use yii\db\Exception;

trait FilterQueryTrait
{
    public function aggregatePrice($function = 'MIN')
    {
        $tableName = Product::tableName();
        $tableNameCur = Currency::tableName();
        /*$this->select(["{$function}(CASE WHEN ({$tableName}.`currency_id`)
                    THEN
                        ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`))
                    ELSE
                        {$tableName}.`price`
                END) AS aggregation_price"]);*/

        if (Yii::$app->db->driverName === 'pgsql') {
            $this->select(["{$function}(CASE WHEN ({$tableName}.currency_id IS NOT NULL)
                    THEN
                        (CASE WHEN ({$tableName}.discount::boolean) THEN
                         ({$tableName}.price * ((SELECT rate::numeric FROM {$tableNameCur} WHERE id=currency_id) - discount::numeric))
                         ELSE
                          ({$tableName}.price * (SELECT rate::numeric FROM {$tableNameCur} WHERE id=currency_id))
                         END)
                        
                    ELSE
                        (CASE WHEN ({$tableName}.discount::boolean) THEN (({$tableName}.price::numeric - {$tableName}.discount::numeric)) ELSE {$tableName}.price::numeric END)
                END) AS aggregation_price"]);
        }else{
            $this->select(["{$function}(CASE WHEN ({$tableName}.currency_id IS NOT NULL)
                    THEN
                        (CASE WHEN ({$tableName}.discount) THEN
                         ({$tableName}.price * ((SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.id={$tableName}.currency_id) - {$tableName}.discount))
                         ELSE
                          ({$tableName}.price * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.id={$tableName}.currency_id))
                         END)
                        
                    ELSE
                        (CASE WHEN ({$tableName}.discount) THEN ({$tableName}.price - {$tableName}.discount) ELSE {$tableName}.price END)
                END) AS aggregation_price"]);
        }


        //$this->orderBy(["aggregation_price" => ($function === 'MIN') ? SORT_ASC : SORT_DESC]);
        $this->distinct(false);
        $this->limit(1);
        //echo $this->createCommand()->rawSql;die;
        return $this;
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
            throw new Exception('applyPrice error operator in ' . __FUNCTION__);
        }
        //$tableName = Product::tableName();
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

        /**$this->select([$tableName . '.*', "(CASE WHEN ({$tableName}.`currency_id`)
                    THEN
                        ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`))
                    ELSE
                        {$tableName}.`price`
                END) AS aggregation_price"]);*/

        $this->select([$tableName . '.*',"(CASE WHEN {$tableName}.`currency_id` IS NOT NULL
                    THEN
                        ({$tableName}.`price` * (SELECT rate FROM {$tableNameCur} WHERE {$tableNameCur}.`id`={$tableName}.`currency_id`))
                    ELSE
                        {$tableName}.`price`
                END) AS aggregation_price"]);

        $this->addOrderBy(["aggregation_price" => $order]); //i change to orderby to addorderby

        return $this;
    }


    public function applyRangePrices($min = 0, $max = 0)
    {
        $cm = Yii::$app->currency;
        if ($cm->active['id'] !== $cm->main['id'] && ($min > 0 || $max > 0)) {
            $min = $cm->activeToMain($min);
            $max = $cm->activeToMain($max);
        }
        if ($min > 0)
            $this->applyPrice($min, '>=');
        if ($max > 0)
            $this->applyPrice($max, '<=');
    }
}