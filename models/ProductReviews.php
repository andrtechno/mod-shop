<?php

namespace panix\mod\shop\models;

use panix\mod\cart\models\OrderProduct;
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

    const STATUS_WAIT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_SPAM = 2;

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
        $rules[] = ['status', 'default', 'value' => self::STATUS_WAIT];
        $rules[] = ['text', 'filter', 'filter' => function ($value) {
            //return Html::encode(HtmlPurifier::process($value));
            return HtmlPurifier::process($value);
        }];
        $rules[] = [['text', 'user_email', 'user_name', 'status'], 'required'];
        $rules[] = [['user_name'], 'string', 'max' => 50];
        $rules[] = [['user_name'], 'string'];
        $rules[] = [['user_email'], 'email'];
        $rules[] = [['user_email', 'user_name'], 'trim'];
        //[['rate'], 'required', 'on' => ['add']],
        $rules[] = [['rate'], 'in', 'range' => [0, 1, 2, 3, 4, 5]];
        $rules[] = ['rate', 'default', 'value' => 0];


        return $rules;
    }


    public function getStatusList()
    {
        return [
            self::STATUS_WAIT => self::t('STATUS_WAIT'),
            self::STATUS_PUBLISHED => self::t('STATUS_PUBLISHED'),
            self::STATUS_SPAM => self::t('STATUS_SPAM'),
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }

    public function getBuy()
    {
        return $this->hasOne(OrderProduct::class, ['product_id' => 'product_id']);
    }

    public function getHasBuy()
    {
        if ($this->user_id) {
            if ($this->buy) {
                return true;
            }
        }
        return false;
    }

    public function afterSave($insert, $changedAttributes)
    {

        if ($insert) {
            $product = Product::findOne($this->product_id);
            $product->rating = $this->rate;
            $product->votes += 1;
            $product->save(false);
        }

        parent::afterSave($insert, $changedAttributes);
    }

    public function getDisplayName()
    {
        return ($this->user_name) ? $this->user_name : $this->user->username;
    }


    public function getHasAnswer()
    {
        return ($this->rgt > 2) ? true : false;
    }

    public function getGridStatusLabel()
    {
        $badge = '';
        if ($this->hasAnswer) {
            $descendants = $this->children()->andWhere(['status' => self::STATUS_WAIT])->count();
            if ($descendants) {
                $badge = $this->getStatusLabel(self::STATUS_WAIT);//Html::tag('span', $this->statusList[self::STATUS_WAIT], ['class' => 'badge badge-danger']);
            }
        } else {
            $badge = $this->getStatusLabel();
        }
        return $badge;
    }

    public function getStatusLabel($value = null)
    {

        $status = (!is_null($value)) ? $value : $this->status;

        $badge = '';
        if ($status == self::STATUS_WAIT) {

            $badge = Html::tag('span', $this->statusList[$status], ['class' => 'badge badge-danger']);
        } elseif ($status == self::STATUS_SPAM) {
            $badge = Html::tag('span', $this->statusList[$status], ['class' => 'badge badge-warning']);
        }

        return $badge;
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
