<?php

namespace panix\mod\shop\models;

use Yii;
use panix\engine\behaviors\nestedsets\NestedSetsBehavior;
use panix\mod\shop\models\query\ProductReviewsQuery;
use panix\mod\user\models\User;
use panix\engine\CMS;
use panix\engine\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/**
 * Class ProductReviews
 * @property integer $id Product id
 * @property integer $manufacturer_id Manufacturer
 * @property integer $type_id Type
 * @property integer $supplier_id Supplier
 * @property integer $currency_id Currency
 * @property Currency $currency
 * @property integer $use_configurations
 * @property string $slug
 * @property string $name Product name
 * @property string $short_description Product short_description
 * @property string $full_description Product full_description
 * @property float $price Price
 * @property float $max_price Max price
 * @property float $price_purchase
 */
class ProductReviews extends ActiveRecord
{

    const route = '/admin/shop/default';
    const MODULE_ID = 'shop';

    public static function find()
    {
        return new ProductReviewsQuery(get_called_class());
    }

    public function init()
    {
        if (!Yii::$app->user->isGuest) {
            $this->user_name = Yii::$app->user->username;
            $this->user_email = Yii::$app->user->email;

        }
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__product_reviews}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {

        $rules = [];
        $rules[] = ['text', 'filter', 'filter' => function ($value) {
            //return Html::encode(HtmlPurifier::process($value));
            return HtmlPurifier::process($value);
        }];
        $rules[] = [['text', 'user_email', 'user_name'], 'required'];
        $rules[] = [['user_name'], 'string', 'max' => 50];
        $rules[] = [['user_name'], 'string'];
        $rules[] = [['user_email'], 'email'];
        $rules[] = [['user_email', 'user_name'], 'trim'];
        //[['rate'], 'required', 'on' => ['add']],
        $rules[] = [['rate'], 'in', 'range' => [0, 1, 2, 3, 4, 5]];
        $rules[] = ['rate', 'default', 'value' => 0];

        return $rules;
    }


    public function beforeValidate()
    {

        return parent::beforeValidate();
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }


    public function afterSave($insert, $changedAttributes)
    {

        if($insert){
            $product = Product::findOne($this->product_id);
            $product->rating = $this->rate;
            $product->votes += 1;
            $product->save(false);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function getDisplayName()
    {
        return ($this->user_id || $this->user) ? $this->user->username : $this->user_name;
    }
    public function behaviors()
    {
        $a = [];
        $a['tree'] = [
            'class' => NestedSetsBehavior::class,
            'hasManyRoots' => true
        ];
        return ArrayHelper::merge($a, parent::behaviors());
    }

}
