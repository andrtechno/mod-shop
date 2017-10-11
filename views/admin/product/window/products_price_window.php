<?php

$form = $this->beginWidget('CActiveForm', array(
    'id' => 'updateprice-form',
    'htmlOptions' => array('class' => '')
        ));
echo Yii::app()->tpl->alert('warning', 'Внимание товары которые привязаны к валюте и/или используют конфигурации изменены не будут', false);
?>



<?=

$form->textField($model, 'price', array(
    'placeholder' => $model->getAttributeLabel('price'),
    'class' => 'form-control'
));
?>
<?= $form->error($model, 'price'); ?>



<?php $this->endWidget(); ?>