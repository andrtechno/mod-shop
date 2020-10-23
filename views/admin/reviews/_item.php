<?php

use panix\engine\Html;
use panix\engine\CMS;

/**
 * @var $model \panix\mod\shop\models\ProductReviews
 * @var $this \yii\web\View
 */

?>

<div class="media pt-3 pb-3" id="comment-<?= $model->id ?>" style="border-bottom: 1px solid rgba(0, 0, 0, 0.1)">
    <a class="mr-3" href="#">
        <?= Html::img($model->user->getAvatarUrl('64x64'), ['class' => 'rounded-circle2']); ?>
    </a>

    <div class="media-body">
        <div class=" row">
            <div class="col-sm-8 d-flex align-items-center">
                <div class="mr-3">
                    <span class="h6">
                        <?php
                        if ($model->user_id) {
                            echo Html::icon('user-outline');
                        }
                        ?>

                        <?= $model->getDisplayName(); ?>

                    </span>
                    <?php if($model->getHasBuy()){ ?>
                        <span class="text-success"><i class="icon-cart"></i> Уже купил(а)</span>
                    <?php } ?>
                </div>
                <div class="mr-3">
                    <?= $model->getStatusLabel(); ?>
                </div>

            </div>
            <div class="col-sm-4 text-right d-flex justify-content-end align-items-center">
                <small class="mr-3"><?= CMS::date($model->created_at); ?></small>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false"><?= Html::icon('menu'); ?></button>
                    <div class="dropdown-menu">
                        <?= Html::a(Html::icon('comments') . ' Ответить', 'javascript:;', ['data-type' => 'ajax', 'data-fancybox' => 'true', 'data-src' => \yii\helpers\Url::to(['reply-add', 'id' => $model->id]), 'class' => 'dropdown-item', 'data-pjax' => 0]); ?>
                        <?php //echo Html::a(Html::icon('comments') . ' Ответить', ['reply', 'id' => $model->id], ['class' => 'dropdown-item', 'id' => 'reply']); ?>
                        <?php if ($model->status != $model::STATUS_WAIT) {
                            echo Html::a(Html::icon('warning') . ' Не публиковать', ['status', 'id' => $model->id, 'status' => 0,'root_id'=>1], ['class' => 'dropdown-item change-status']);
                        }
                        if ($model->status != $model::STATUS_PUBLISHED) {
                            echo Html::a(Html::icon('check') . ' Опубликовать', ['status', 'id' => $model->id, 'status' => 1,'root_id'=>1], ['class' => 'dropdown-item change-status']);
                        }

                        if ($model->status != $model::STATUS_SPAM) {
                            echo Html::a(Html::icon('trashcan') . ' SPAM', ['status', 'id' => $model->id, 'status' => 2,'root_id'=>1], ['class' => 'dropdown-item change-status']);
                        }
                        echo Html::a(Html::icon('delete') . ' Удалить', ['delete', 'id' => $model->id], ['class' => 'dropdown-item delete', 'data-pjax' => 0]);
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-muted" id="comment_text_<?= $model->id; ?>"><?= nl2br(Html::text($model->text)); ?></div>
        <div class="container-reply" id="container-reply-<?= $model->id ?>">
            <?php
            $descendants = $model->children()->orderBy(['id' => SORT_DESC])->all();
            foreach ($descendants as $data) {
                echo $this->render('_item', ['model' => $data]);
            }
            ?>

        </div>
    </div>
</div>
