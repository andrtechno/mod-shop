<?php

use yii\helpers\Html;
use yii\widgets\DetailView;



$this->view->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app/default', 'Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->view->title;
?>
<div class="pages-view">

    <h1><?= Html::encode($this->view->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app/default', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app/default', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app/default', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
        ],
    ]) ?>

</div>
