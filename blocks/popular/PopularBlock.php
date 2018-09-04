<?php

namespace panix\mod\shop\blocks\popular;

use panix\mod\shop\models\Product;
use panix\engine\data\ActiveDataProvider;

class PopularBlock extends \panix\engine\data\Widget {

    public $limiter = 10;

    public function run() {
        $query = Product::find();
        $query->limit($this->limiter);
        $query->orderBy('views');
        //$query->joinWith('translations');
        //$query->with('translations');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => Product::getSort(),
            'pagination' => false
                ]
        );
        return $this->render($this->skin, ['dataProvider' => $dataProvider]);
    }

}
