<?php

use yii\helpers\Html;
use panix\engine\bootstrap\ActiveForm;


?>

<?php
$form = ActiveForm::begin([
    'options' => ['class' => 'form-horizontal']
]);
?>
<div class="card">
    <div class="card-header">
        <h5 class="card-title"><?= $this->context->pageName ?></h5>
    </div>
    <div class="card-body">
        <?php
        echo yii\bootstrap4\Tabs::widget([
            'items' => [
                [
                    'label' => 'Общие',
                    'content' => $this->render('_global', ['form' => $form, 'model' => $model]),
                    'active' => true,
                    'options' => ['id' => 'global'],
                ],
                [
                    'label' => 'Формат цены',
                    'content' => $this->render('_price', ['form' => $form, 'model' => $model]),
                    'options' => ['id' => 'price'],
                ],
            ],
        ]);
        ?>
    </div>
    <div class="card-footer text-center">
        <?= $model->submitButton(); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php

if(isset(Yii::$app->queue)){
    $query = (new \yii\db\Query())
       // ->where(['channel' => Yii::$app->queue->channel])
        ->from(Yii::$app->queue->tableName);

    $qqq = $query->all();
    foreach ($qqq as $item) {

      //  \panix\engine\CMS::dump(Yii::$app->queue->isWaiting($item['id']));

// Check whether a worker got the job from the queue and executes it.
       // \panix\engine\CMS::dump(Yii::$app->queue->isReserved($item['id']));

// Check whether a worker has executed the job.
      //  \panix\engine\CMS::dump(Yii::$app->queue->isDone($item['id']));
        var_dump(Yii::$app->queue->isDone($item['id']));
        echo '<br>';


    }
}
?>



