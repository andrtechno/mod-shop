<?php

namespace panix\mod\shop\widgets\search;

use Yii;
use panix\engine\data\Widget;

class SearchWidget extends Widget {

    public function run() {
        $value = (Yii::$app->request->get('q')) ? Yii::$app->request->get('q') : '';
        return $this->render($this->skin, ['value' => $value]);
    }

}
