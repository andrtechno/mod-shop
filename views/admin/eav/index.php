<?php

echo \mirocow\eav\admin\widgets\Fields::widget([
    'model' => new \panix\mod\shop\models\ShopProduct,
   // 'categoryId' => $model->mainCategory->id,
    'entityName' => 'product',
    'entityModel' => \panix\mod\shop\models\ShopProduct::className(),
]);
?>