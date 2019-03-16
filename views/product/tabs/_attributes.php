
<?php


echo panix\mod\shop\components\AttributesRender::widget([
    'model' => $model,
    'view' => '_attributes_group',
    'htmlOptions' => array(
        'class' => 'attributes'
    ),
]);
?>