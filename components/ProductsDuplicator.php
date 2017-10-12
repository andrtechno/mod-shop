<?php

namespace panix\mod\shop\components;

use Yii;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\RelatedProduct;
use panix\mod\shop\models\ProductVariant;
use panix\engine\CMS;

class ProductsDuplicator extends \yii\base\Component {

    /**
     * @var array
     */
    private $_ids;

    /**
     * @var array
     */
    private $duplicate;

    /**
     * @var string to be appended to the end of product name
     */
    private $_suffix;

    public function __construct() {
        $this->_suffix = ' (' . Yii::t('shop/admin', 'копия') . ')';
    }

    /**
     * Creates copy of many products.
     *
     * @param array $ids of products to make copy
     * @param array $duplicate list of product parts to copy: images, variants, etc...
     * @return array of new product ids
     */
    public function createCopy(array $ids, array $duplicate = array()) {

        $this->duplicate = $duplicate;
        $new_ids = array();

        foreach ($ids as $id) {
            $model = Product::findOne($id);

            if ($model) {
                $new_ids[] = $this->duplicateProduct($model)->id;
            }
        }

        return $new_ids;
    }

    /**
     * Duplicate one product and return model
     *
     * @param Product $model
     * @return Product
     */
    public function duplicateProduct(Product $model) {

        $product = new Product;
        $product->attributes = $model->attributes;

        $behaviors = $model->behaviors();


        foreach ($behaviors['translate']['translationAttributes'] as $attr)
            $product->$attr = $model->$attr;

        $product->name .= $this->getSuffix();
        $product->seo_alias .= CMS::translit($this->getSuffix()) . '-' . time();
        $product->main_category_id = $model->mainCategory->id;

        $product->scenario = 'duplicate';
        if ($product->validate()) {
            if ($product->save()) {
                foreach ($this->duplicate as $feature) {
                    $method_name = 'copy' . ucfirst($feature);

                    if (method_exists($this, $method_name))
                        $this->$method_name($model, $product);
                }
                $product->setCategories(array(), $model->mainCategory->id);
                return $product;
            }else {
                die(__FUNCTION__ . ': Error save');
                return false;
            }
        } else {

            print_r($product->getErrors());
            die;
        }
    }

    /**
     * Creates copy of product images
     *
     * @param Product $original
     * @param Product $copy
     */
    protected function copyImages(Product $original, Product $copy) {
        $images = $original->attachments;

        if (!empty($images)) {
            foreach ($images as $image) {
                $image_copy = new AttachmentModel();



                $image_copy->product_id = $copy->id;
                $image_copy->name = $copy->id . '_' . $image->name;
                $image_copy->is_main = $image->is_main;
                $image_copy->user_id = $image->user_id;
                $image_copy->title = $image->title;


                if ($image_copy->validate()) {
                    if ($image_copy->save()) {
                        copy($image->filePath, $image_copy->filePath);
                    } else {
                        die(__FUNCTION__ . ': Error save');
                    }
                } else {
                    die(__FUNCTION__ . ': Error validate');
                }
            }
        }
    }

    /**
     * Creates copy of EAV attributes
     *
     * @param Product $original
     * @param Product $copy
     */
    protected function copyAttributes(Product $original, Product $copy) {
        $attributes = $original->getEavAttributes();

        if (!empty($attributes)) {
            foreach ($attributes as $key => $val) {
                Yii::$app->db->createCommand()->insert('{{%shop_product_attribute_eav}}', [
                    'entity' => $copy->id,
                    'attribute' => $key,
                    'value' => $val
                ])->execute();
            }
        }
    }

    /**
     * Copy related products
     *
     * @param Product $original
     * @param Product $copy
     */
    protected function copyRelated(Product $original, Product $copy) {
        $related = $original->related;

        if (!empty($related)) {
            foreach ($related as $p) {
                $model = new RelatedProduct();
                $model->product_id = $copy->id;
                $model->related_id = $p->related_id;
                $model->save();
                //двустороннюю связь между товарами
                if (Yii::$app->settings->get('shop', 'product_related_bilateral')) {
                    $related = new RelatedProduct;
                    $related->product_id = $p->related_id;
                    $related->related_id = $copy->id;
                    $related->save();
                }
            }
        }
    }

    /**
     * Copy product variants
     *
     * @param Product $original
     * @param Product $copy
     */
    public function copyVariants(Product $original, Product $copy) {
        $variants = $original->variants;

        if (!empty($variants)) {
            foreach ($variants as $v) {
                $record = new ProductVariant();
                $record->product_id = $copy->id;
                $record->attribute_id = $v->attribute_id;
                $record->option_id = $v->option_id;
                $record->price = $v->price;
                $record->price_type = $v->price_type;
                $record->sku = $v->sku;
                $record->save();
            }
        }
    }

    /**
     * @param $str string product suffix
     */
    public function setSuffix($str) {
        $this->_suffix = $str;
    }

    /**
     * @return string
     */
    public function getSuffix() {
        return $this->_suffix;
    }

}
