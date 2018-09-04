<?php

use panix\engine\Html;
use panix\ext\fancybox\Fancybox;
use panix\mod\images\models\ImageSearch;
use panix\engine\widgets\Pjax;
?>
<?= Fancybox::widget(['target' => 'a.fancybox']); ?>

<?= $form->field($model, 'file[]')->fileInput(['multiple' => true]); ?>



<?php

$script = <<< JS
$('.attachment-delete').on('click', function(e) {
    var id = $(this).attr('data-id');
    var model = $(this).attr('data-model');
    var object_id = $(this).attr('data-object_id');
    $.ajax({
       url: $(this).attr('href'),
       type:'POST',
       data: {id: id, model: model, object_id: object_id},
       dataType:'json',
       success: function(data) {
            if(data.status == "success"){
                common.notify(data.message,"success");
                $('tr[data-key="'+id+'"]').remove();
        $('#grid-images').yiiGridView('applyFilter');
        
                common.removeLoader();
            }
       }
    });
    return false;
});
        
        
        
        $('.copper').on('click', function(e) {
      //  var id = $(this).attr('data-id');
    $.ajax({
       url: $(this).attr('href'),
       type:'POST',
      // data: {id: id},
       success: function(data) {
        $('#cropper-body').html(data);
        $('#cropper-modal').modal('show')
       }
    });
        return false;
});
JS;
$this->registerJs($script); //$position
?>
<style>
.modal .modal-dialog   {
  width: 750px;
  margin: auto;
}

    
</style>
    <?php
use yii\bootstrap4\Modal;
Modal::begin([
    'options'=>['id'=>'cropper-modal','style'=>'width:100%'],
    'header' => '<h2>Cropper</h2>',
    'toggleButton' => false,
    'bodyOptions'=>['id'=>'cropper-body']
]);

echo 'Say hello...';

Modal::end();

?>


<?php

$searchModel = new ImageSearch();
$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), ['model' => $model]);


/* Pjax::begin([
  'timeout' => 50000,
  'id' => 'pjax-' . strtolower((new \ReflectionClass($searchModel))->getShortName()),
  //'id' => 'pjax-' . strtolower((new \ReflectionClass(new \panix\mod\images\models\Image))->getShortName()),
  'linkSelector' => 'a:not(.linkTarget)'
  //'id' => 'pjax-image-container',
  //'enablePushState' => false,
  //  'linkSelector' => 'a:not(.linkTarget)'
  ]); */
echo panix\engine\grid\GridView::widget([
    'id' => 'grid-images',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'rowOptions' => ['class' => 'sortable-column'],
    'enableLayout' => false,
    //'layout'=>'{items}',
    'rowOptions' => function ($model, $index, $widget, $grid) {
        $coverClass = ($model->is_main) ? 'bg-success active sortable-column' : 'sortable-column';
        return ['class' => $coverClass];
    },
    'columns' => [
        [
            'class' => 'panix\engine\grid\sortable\Column',
            'url' => ['/images/default/sortable'],
            'successMessage' => Yii::t('shop/admin', 'SORT_IMAGE_SUCCESS_MESSAGE')
        ],
        [
            'attribute' => 'image',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center image'],
            'value' => function($model) {
                return Html::a(Html::img($model->getUrl('100x100'), ['class' => '']), $model->getUrl(), ['class' => 'img-thumbnail fancybox']);
            },
        ],
        [
            'attribute' => 'is_main',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
                return Html::radio('AttachmentsMainId', $model->is_main, [
                            'value' => $model->id,
                            'class' => 'check',
                            'data-toggle' => "tooltip",
                            'title' => Yii::t('app', 'IS_MAIN'),
                            'id' => 'main_image_' . $model->id
                ]);
            },
        ],
        [
            'attribute' => 'alt_title',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function($model) {
                return Html::textInput('attachment_image_titles[' . $model->id . ']', $model->alt_title, array('class' => 'form-control'));
            },
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{resize} {settings} {delete}',
            'filter' => false,
            'buttons' => [
                'resize' => function ($url, $data, $key) {
                    return Html::a(Html::icon('resize'), ['s'], array('class' => 'btn btn-sm btn-default attachment-zoom', 'data-fancybox' => 'gallery'));
                },
                'settings' => function ($url, $data, $key) {
                    return Html::a(Html::icon('settings'), ['/images/edit-crop', 'id' => $data->id], array('class' => 'btn btn-sm btn-default copper'));
                },
                'delete' => function ($url, $data, $key) use ($model) {
                    return Html::a(Html::icon('delete'), ['/images/default/delete', 'id' => $data->id], array('class' => 'btn btn-sm btn-danger attachment-delete linkTarget', 'data-id' => $data->id, 'data-object_id' => $model->id, 'data-model' => get_class($model)));
                },
            ]
        ]
    ],
    'filterModel' => $searchModel
]);
//Pjax::end();
?>









