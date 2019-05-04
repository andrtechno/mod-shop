<?php

namespace panix\mod\shop\models;

use Yii;
use panix\engine\db\ActiveRecord;

class Supplier extends ActiveRecord
{

    const MODULE_ID = 'shop';
    const route = '/admin/shop/supplier';

    public function getGridColumns()
    {
        return [
            'name' => [
                'attribute' => 'name',
                'contentOptions' => ['class' => 'text-left'],
            ],
            'address' => [
                'attribute' => 'address',
                'contentOptions' => ['class' => 'text-left'],
            ],
            'phone' => [
                'attribute' => 'phone',
                //'format' => 'tel',
                'contentOptions' => ['class' => 'text-center'],
            ],
            'email' => [
                'format'=>'email',
                'attribute' => 'email',
                'contentOptions' => ['class' => 'text-center'],
            ],
            'DEFAULT_CONTROL' => [
                'class' => 'panix\engine\grid\columns\ActionColumn',
            ],
            'DEFAULT_COLUMNS' => [
                ['class' => 'panix\engine\grid\columns\CheckboxColumn'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop__suppliers}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address', 'phone', 'name'], 'required'],
            [['address', 'name'], 'trim'],
            ['phone', '\panix\ext\telinput\PhoneInputValidator'],
            [['email'], 'email'],
            [['address'], 'string'],
            [['address'], 'string', 'max' => 255],
            [['name', 'address'], 'safe'],
        ];
    }


}
