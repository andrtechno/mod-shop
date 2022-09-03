<?php

namespace panix\mod\shop\api\v1\models;


use Yii;
use panix\mod\shop\models\Category as BaseCategory;


class Category extends BaseCategory
{
    public function fields()
    {
        $data = [];
        return [
            'id',
            'name',
            'created_at',
            'updated_at',
            'switch'
        ];
    }

}
