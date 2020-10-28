<?php
/**
 * @var \app\modules\reviews\models\Reviews $model
 */

$hasAnswer = ($model->rgt > 2) ? true : false;
?>
<div class="review <?= $hasAnswer ? 'review-answer' : ''; ?>">
    <div class="row">
        <div class="col-4 col-lg-2">
            <?php if ($model->user_id) { ?>
                <div class="review-raiting">
                    <?php
                    echo \panix\ext\rating\RatingInput::widget([
                        'model' => $model,
                        'attribute' => 'rate',
                        //'jsonld'=>false,
                        'options' => [
                            'readOnly' => true,
                            'path' => $this->theme->asset[1] . '/img/',
                            'starOff' => 'star-off.svg',
                            'starOn' => 'star-on.svg',
                            'hints' => [
                                Yii::t('default', 'RATING_1'),
                                Yii::t('default', 'RATING_2'),
                                Yii::t('default', 'RATING_3'),
                                Yii::t('default', 'RATING_4'),
                                Yii::t('default', 'RATING_5'),
                            ],
                        ]
                    ]);
                    ?>
                </div>
            <?php } ?>
            <div class="review-info">
                <div class="review-name"><?= $model->getDisplayName(); ?></div>
                <div class="review-date"><?= \panix\engine\CMS::date($model->created_at, false); ?></div>
                <?php if ($model->user_id) { ?>
                    <ul class="social clearfix">
                        <li><a class="fb" href="/"></a></li>
                        <li><a class="inst" href="/"></a></li>
                    </ul>
                <?php } ?>
            </div>
        </div>
        <div class="col-8 col-lg-10">
            <p class="review-txt"><?= $model->text; ?></p>
        </div>
    </div>
    <?php
    // print_r($model->query);

    if ($hasAnswer) {
        $descendants = $model->children()->orderBy(['id' => SORT_DESC])->all();
        ?>
        <div class="row">
            <div class="col-lg-11 offset-lg-1">
                <?php
                foreach ($descendants as $data) { ?>


                    <?= $this->render('_comment_answer', ['model' => $data]); ?>


                <?php } ?>

            </div>
        </div>
    <?php } ?>
</div>
