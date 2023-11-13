<?php

namespace panix\mod\shop\models\query;


use Yii;
use yii\db\ActiveQuery;
use panix\engine\traits\query\DefaultQueryTrait;
use panix\mod\shop\models\traits\EavQueryTrait;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductCategoryRef;
use panix\engine\taggable\TaggableQueryBehavior;

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

    public function behaviors()
    {
        return [
            TaggableQueryBehavior::class,
        ];
    }
    /**
     * Default sorting
     */
    public function sortAvailability()
    {
        /** @var \yii\db\ActiveRecord $modelClass */
        $modelClass = $this->modelClass;
        $tableName = $modelClass::tableName();
        return $this->addorderBy("(CASE {$tableName}.availability WHEN " . Product::STATUS_OUT_STOCK . " then -1 END) ASC");
        //parent::init();
    }

    public function sales()
    {


        $this->andWhere(['IS NOT', Product::tableName() . '.discount', null])
            ->andWhere(['!=', Product::tableName() . '.discount', '']);
        $this->andWhere(['!=', Product::tableName().".availability", Product::STATUS_OUT_STOCK]);
        return $this;
    }

    /**
     * @return $this
     */
    public function new()
    {
        $config = Yii::$app->settings->get('shop');
        if ($config->label_expire_new) {
            $date_utc = new \DateTime("now", new \DateTimeZone("UTC"));
            $now =$date_utc->getTimestamp();
            $modelClass = $this->modelClass;
            $tableName = $modelClass::tableName();
            $this->andWhere(['!=', Product::tableName().".availability", Product::STATUS_OUT_STOCK]);
            $this->andWhere(['>=', $tableName . '.created_at', ($date_utc->getTimestamp() - (86400 * $config->label_expire_new))]);
        } else {
            //$this->int2between(-1, -1);
        }
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function topSales()
    {
        $config = Yii::$app->settings->get('shop');
        if ($config->added_to_cart_count && $config->added_to_cart_period) {
            //$this->where(['like', 'label', 'hit_sale'])
            //$this->int2between(time() - (86400 * $offset), time(), 'added_to_cart_date');
            //$this->orWhere(['>=', 'added_to_cart_count', $config->added_to_cart_count]);
            $this->where(['>=', 'added_to_cart_count', $config->added_to_cart_count]);
            $this->andWhere(['>=', 'added_to_cart_date', time() - (86400 * (int)$config->added_to_cart_period)]);
            $this->addOrderBy(['added_to_cart_count' => SORT_DESC]);
            $this->andWhere(['!=', Product::tableName().".availability", Product::STATUS_OUT_STOCK]);
        }
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
        $brands = array_map('intval', $brands);
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
        if (!$ref) {
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

    /**
     * @param int $current_id
     * @return $this
     */
    public function views($current_id = 0)
    {
        $session = Yii::$app->session->get('views');
        if (!empty($session)) {
            $ids = array_unique($session);
            if ($current_id) {
                $key = array_search($current_id, $ids);
                unset($ids[$key]);
            }
            $this->andWhere(['id' => $ids]);
        }
        return $this;
    }
}
