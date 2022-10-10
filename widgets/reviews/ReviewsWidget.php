<?php

namespace panix\mod\shop\widgets\reviews;

use Yii;
use yii\base\Widget;
use panix\mod\shop\models\ProductReviews;

class ReviewsWidget extends Widget
{
    public $skin = 'default';
    public $itemView = '_item';
    public $model;

    public function run()
    {

        $reviewModel = new ProductReviews;
        $provider = new \panix\engine\data\ActiveDataProvider([
            'query' => $this->model->getReviews()->status(1)->roots(),
            'pagination' => [
                'pageSize' => 50,
            ]
        ]);


        $value = '';
        return $this->render($this->skin, [
            'model'=>$this->model,
            'provider' => $provider,
            'reviewModel' => $reviewModel
        ]);
    }

}
