<?php
$descendants = $model->children()->status(1)->orderBy(['created_at'=>SORT_DESC])->all();
/**
 * @var $model \panix\mod\shop\models\ProductReviews
 * @var $this \yii\web\View
 */
?>
<div class="item-tab-content review">
    <div class="row customer-review">
        <div class="col-2">
            <div class="customer">
                <h5 class="name"><?= $model->getDisplayName(); ?></h5>
                <?php
                if ($model->depth == 1) {
                    if ($model->user_id) {

                        echo \panix\ext\rating\RatingInput::widget([
                            'model' => $model,
                            'attribute' => 'rate',
                            'options' => [
                                'readOnly' => true,
                                'starType' => 'img',
                                'path' => $this->theme->asset[1] . '/images/',
                                'starOff' => 'star-off.svg',
                                'starOn' => 'star-on.svg',
                                'hints' => [
                                    Yii::t('reviews/default', 'RATING_1'),
                                    Yii::t('reviews/default', 'RATING_2'),
                                    Yii::t('reviews/default', 'RATING_3'),
                                    Yii::t('reviews/default', 'RATING_4'),
                                    Yii::t('reviews/default', 'RATING_5'),
                                ],
                            ]
                        ]);
                    } else {
                        echo '<span class="text-muted">' . Yii::t('app/default', 'GUEST') . '</span>';
                    }
                }
                ?>
            </div>
        </div>
        <div class="col-10">
            <div class="customer-review review <?= ($descendants) ? 'review-answer' : ''; ?>">
                <div class="review-content">
                    <div class="date"><?= \panix\engine\CMS::date($model->created_at, false); ?></div>
                    <div class="content review-txt"><?= $model->text; ?></div>
                </div>
            </div>
        </div>
        <?php if ($descendants) { ?>
        <div class="col-lg-10 offset-2">
            <?php
            foreach ($descendants as $data) { ?>
                <?= $this->render('_comment_item', ['model' => $data]); ?>
            <?php } ?>
        </div>
        <?php } ?>

    </div>
</div>
