
<?php
use yii\helpers\Html;

echo Html::beginForm('', 'post', array(
    'id' => 'ProductTypeForm',
    'class' => 'form-horizontal'
));
//echo Html::errorSummary($model);

echo Html::hiddenInput('main_category', $model->main_category,['id'=>'main_category']);


        echo yii\bootstrap\Tabs::widget([
          
            'items' => [
                [
                    'label' => 'OPTIONS',
                    'content' => $this->render('tabs/_options', ['attributes' => $attributes, 'model' => $model]),
                    'headerOptions' => [],
                                'active' => true,
                    'options' => ['id' => 'options'],
                ],
                [
                    'label' => 'Категории',
                    'content' => $this->render('tabs/_tree', ['model' => $model]),
        
                    'options' => ['id' => 'tree'],
                ],


            ],
        ]);
        

?>

<div class="form-group text-center">
<?= Html::submitButton(Yii::t('app', 'SAVE'), array('class' => 'btn btn-success')); ?>
</div>

<?php echo Html::endForm();

?>

