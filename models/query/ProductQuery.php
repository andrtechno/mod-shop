<?php

namespace panix\mod\shop\models\query;

use Yii;
use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;
use panix\engine\traits\query\TranslateQueryTrait;
use panix\mod\shop\models\traits\EavQueryTrait;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductCategoryRef;

class ProductQuery extends ActiveQuery
{

    use DefaultQueryTrait, EavQueryTrait, FilterQueryTrait;

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

    public function sales()
    {

        $this->andWhere(['IS NOT', Product::tableName() . '.discount', null]);
        //->andWhere(['!=', Product::tableName() . '.discount', '']);
        return $this;
    }

    public function new()
    {
        $config = Yii::$app->settings->get('shop');
        if ($config->label_expire_new) {
            $this->int2between(time(), time() - (86400 * $config->label_expire_new * 300));
        } else {
            $this->int2between(-1, -1);
        }
        $this->orderBy([Product::tableName().'.created_at' => SORT_DESC]);
        return $this;
    }


    /**
     * @param $brands array|int
     * @param $whereType string
     * @return $this
     */
    public function applyBrands($brands, $whereType = 'andWhere')
    {
        if (!is_array($brands))
            $brands = [$brands];

        if (empty($brands))
            return $this;

        sort($brands);

        $this->$whereType(['brand_id' => $brands]);
        return $this;
    }

    /**
     * @param $suppliers array|int
     * @return $this
     */
    public function applySuppliers($suppliers)
    {
        if (!is_array($suppliers))
            $suppliers = [$suppliers];

        if (empty($suppliers))
            return $this;

        sort($suppliers);

        $this->andWhere(['supplier_id' => $suppliers]);
        return $this;
    }

    /**
     * @param $categories array|int|object
     * @param $whereType string
     * @param $ref boolean
     * @return $this
     */
    public function applyCategories($categories, $whereType = 'andWhere', $ref = false)
    {

        if ($categories instanceof Category)
            $categories = [$categories->id];
        else {
            if (!is_array($categories))
                $categories = [$categories];
        }
        if ($ref) {
            $this->$whereType(['main_category_id' => $categories]);
        } else {
            //  $tableName = ($this->modelClass)->tableName();
            $this->leftJoin(ProductCategoryRef::tableName(), ProductCategoryRef::tableName() . '.`product`=' . $this->modelClass::tableName() . '.`id`');
            $this->$whereType([ProductCategoryRef::tableName() . '.`category`' => $categories]);
        }
        return $this;
    }


    /**
     * Product by brand
     *
     * @return $this
     */
    public function brand()
    {
        $this->joinWith(['brand']);
        return $this;
    }


    /**
     * @param null $q
     * @return $this
     */
    public function applySearch($q = null)
    {
        $language = Yii::$app->language;
        if ($q) {
            $modelClass = $this->modelClass;
            $tableName = $modelClass::tableName();
            $this->andWhere(['LIKE', $tableName . '.' . Yii::$app->getModule('shop')->searchAttribute, $q])
                ->orWhere(['LIKE', $tableName . '.name_' . $language, $q]);

        }
        return $this;
    }
    /*
        public function new($start, $end)
        {
            $modelClass = $this->modelClass;
            $tableName = $modelClass::tableName();
            $this->between($start, $end, 'created_at');
            return $this;
        }*/


    /**
     * @param integer $current_id
     * @param array $wheres
     * @return $this
     */
    public function next($current_id, $wheres = [])
    {
        $modelClass = $this->modelClass;
        $tableName = $modelClass::tableName();

        $subQuery = (new \yii\db\Query())->select('MIN(`id`)')
            ->from($tableName . ' next')
            ->where(['>', 'next.id', $current_id]);

        if ($wheres) {
            $subQuery->andWhere($wheres);
        }

        $this->where(['=', 'id', $subQuery]);

        return $this;
    }

    /**
     * @param integer $current_id
     * @param array $wheres
     * @return $this
     */
    public function prev($current_id, $wheres = [])
    {
        $modelClass = $this->modelClass;
        $tableName = $modelClass::tableName();

        $subQuery = (new \yii\db\Query())->select('MAX(`id`)')
            ->from($tableName . ' prev')
            ->where(['<', 'prev.id', $current_id]);

        if ($wheres) {
            $subQuery->andWhere($wheres);
        }

        $this->where(['=', 'id', $subQuery]);

        return $this;
    }


}
