<?php

namespace panix\mod\shop\blocks\popular;

use panix\mod\shop\models\ShopProduct;
use panix\engine\data\ActiveDataProvider;

class PopularBlock extends \panix\engine\data\Widget {

    public $limiter = 11;

    public function run() {
        $query = ShopProduct::find();
        $query->limit($this->limiter);
        $query->orderBy('views');
        //$query->joinWith('translations');
        //$query->with('translations');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ShopProduct::getSort(),
            'pagination' => false
                ]
        );
        return $this->render($this->skin, ['dataProvider' => $dataProvider]);
    }

}
