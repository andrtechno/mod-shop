<?php
/**
 * @var \app\modules\reviews\models\Reviews $model
 */


?>
<div class="review">
    <div class="row">
        <div class="col-4 col-lg-2">
            <div class="review-raiting">
                <?php
                echo \panix\ext\rating\RatingInput::widget([
                    'model' => $model,
                    'attribute' => 'rate',
                    'options' => [
                        'readOnly' => true,
                        'starOff' => $this->theme->asset[1] . '/img/star-off.svg',
                        'starOn' => $this->theme->asset[1] . '/img/star-on.svg',

                    ]
                ]);
                ?>
            </div>
            <div class="review-info">
                <div class="review-name"><?= $model->getDisplayName(); ?></div>
                <div class="review-date"><?= \panix\engine\CMS::date($model->created_at, false); ?></div>
                <ul class="social clearfix">
                    <li><a class="fb" href="/"></a></li>
                    <li><a class="inst" href="/"></a></li>
                </ul>
            </div>
        </div>
        <div class="col-8 col-lg-10">
            <p class="review-txt"><?= $model->text; ?></p>
        </div>
    </div>
</div>
