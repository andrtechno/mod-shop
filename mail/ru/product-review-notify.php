<?php
use yii\helpers\Url;
use panix\engine\Html;

/**
 * @var $site_name string
 */

?>
<h3>Оставлен новай отзыв о товаре</h3>

<p><strong>Оценка</strong>: <?= $model->rate; ?>/5</p>
<p><strong>От</strong>: <?= $model->getDisplayName(); ?> (<?= $model->user_email; ?>)</p>
<p><strong>Товар</strong>: <?= Html::a($model->product->name, Url::to($model->product->getUrl(), true), ['target' => '_blank']); ?></p>

<p><strong>Отзыв</strong>: <?= Html::a('Редактировать отзыв', Url::to(['/admin/shop/reviews/update','id'=>$model->id], true), ['target' => '_blank']); ?>
<br/><?= $model->text; ?></p>
