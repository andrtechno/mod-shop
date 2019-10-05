<?php
echo \panix\engine\widgets\ListView::widget([
    'dataProvider' => $provider,
    'itemView' => '@shop/views/category/'.$itemView,
    'layout' => '{summary}{items}{pager}',
    'itemOptions' => ['class' => 'item'],
    'options' => ['class' => 'list-view clearfix _view_grid'],
    //'summaryOptions' => ['class' => 'summary'],
    'emptyTextOptions' => ['class' => 'col-sm-12 alert alert-info'],
    //'beforeItem'=>function ($model, $key, $index, $widget){
    //return 'ss';
    //}
    'pager' => [
        'options' => ['class' => 'col-sm-12 pagination']
    ]
]);
?>