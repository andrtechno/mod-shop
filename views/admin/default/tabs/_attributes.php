

<?php

foreach ($model->getEavAttributes()->all() as $attr) {

    echo $attr->name;
    echo $form->field($model, $attr->name, ['class' => '\mirocow\eav\widgets\ActiveField'])->eavInput();
}
echo $model->c50;
?>
