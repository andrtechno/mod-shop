<?php

namespace panix\mod\shop\models\query;

use yii\db\ActiveQuery;

class ShopProductQuery extends ActiveQuery {

    public function published($state = 1) {
        return $this->andWhere(['switch' => $state]);
    }

    /**
     * Product by category
     *
     * @return $this
     */
    public function category() {
        $this->joinWith(['category']);
        return $this;
    }

    public function applyManufacturers($manufacturers) {
        if (!is_array($manufacturers))
            $manufacturers = array($manufacturers);

        if (empty($manufacturers))
            return $this;

        $this->andWhere(['`manufacturer_id`' => $manufacturers]);
        return $this;
    }

    public function applyCategories($categories) {
        if ($categories instanceof \panix\mod\shop\models\ShopCategory)
            $categories = array($categories->id);
        else {
            if (!is_array($categories))
                $categories = array($categories);
        }

        $this->leftJoin('{{%shop_product_category_ref}}', '{{%shop_product_category_ref}}.`product`={{%shop_product}}.`id`');
        $this->andWhere(['{{%shop_product_category_ref}}.`category`' => $categories]);

        return $this;
    }

    /**
     * Product by manufacturer
     *
     * @return $this
     */
    public function manufacturer() {
        $this->joinWith(['manufacturer']);
        return $this;
    }

}
