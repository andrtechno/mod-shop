<?php

namespace panix\mod\shop\models\query;

use yii\db\ActiveQuery;

class ShopProductQuery extends ActiveQuery {

    public function published($state = 1) {
        return $this->andWhere(['{{%shop_product}}.switch' => $state]);
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

    public function applyAttributes(array $attributes) {
        if (empty($attributes))
            return $this;
        return $this->withEavAttributes($attributes);
    }

    public function withEavAttributes($attributes = array()) {
        // If not set attributes, search models with anything attributes exists.
        if (empty($attributes)) {
            $attributes = $this->getSafeAttributesArray();
        }

        // $attributes be array of elements: $attribute => $values
        return $this->getFindByEavAttributes($attributes);
    }

    protected function getFindByEavAttributes($attributes) {
        $pk = '{{%shop_product}}.id';
        $i = 0;
        foreach ($attributes as $attribute => $values) {
            // If search models with attribute name with specified values.
            if (is_string($attribute)) {
                // Get attribute compare operator
                if (!is_array($values)) {
                    $values = array($values);
                }

                foreach ($values as $value) {
                    $this->join('JOIN', '{{%shop_product_attribute_eav}} eavb' . $i, "{$pk}=`eavb{$i}`.`entity`");
                    $this->andWhere(['IN', "`eavb$i`.`value`", $values]);
                    $i++;
                }
            }
            // If search models with attribute name with anything values.
            elseif (is_int($attribute)) {
                $this->join('JOIN', '{{%shop_product_attribute_eav}} eavb' . $i, "$pk=`eavb$i`.`entity` AND eavb$i.attribute = $values");
                $i++;
            }
        }

        $this->distinct(true);
        $this->groupBy("{$pk}");
        // echo $this->createCommand()->getRawSql();die;
        return $this;
    }

    /**
     * Filter products by min_price
     * @param $value
     */
    public function applyMinPrice($value) {
        if ($value) {
            $this->andWhere(['>=', 'price', (int) $value]);
        }
        return $this;
    }

    /**
     * Filter products by max_price
     * @param $value
     */
    public function applyMaxPrice($value) {
        if ($value) {
            $this->andWhere(['<=', 'price', (int) $value]);
        }
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
