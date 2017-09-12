<?php

namespace panix\mod\shop\widgets\search;

use Yii;

class SearchWidget extends \panix\engine\data\Widget {

    public function run() {
        $value = (Yii::$app->request->get('q')) ? Yii::$app->request->get('q') : '';
        return $this->render($this->skin, ['value' => $value]);
    }

}
