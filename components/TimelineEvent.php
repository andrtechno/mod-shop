<?php

namespace panix\mod\shop\components;

use yii\base\Event;

class TimelineEvent extends Event
{
    public $params = [];
    public $callback = 'timeline';

    public function onAddReview()
    {
        return 'Оставил отзыв';
    }

}
