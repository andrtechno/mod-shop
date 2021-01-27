<?php

use panix\engine\Html;
use panix\ext\fancybox\Fancybox;
use panix\mod\images\models\ImageSearch;
use panix\engine\widgets\Pjax;
use panix\engine\bootstrap\Modal;
use panix\engine\CMS;

/**
 * @var $form \panix\engine\bootstrap\ActiveForm
 * @var $model \panix\mod\shop\models\Product
 */
if ($model->video) {
    $video= '<br/>'.Html::a(Html::img(CMS::getYouTubeImg($model->video, 'mqdefault'), ['class' => 'img-fluid']), $model->video, ['class' => 'video']);
}else{
    $video='';
}
?>
<?php echo Fancybox::widget([
    'target' => 'a.fancybox, a.video',
    'options' => [
        'youtube' => [
            'autoplay' => 0,
            'controls' => 1,
            'showinfo' => 1
        ]
    ]
]); ?>
<?= $form->field($model, 'video')->textInput(['maxlength' => 255])->hint('Пример: https://www.youtube.com/watch?v=XXXXXX'.$video) ?>

<?php


?>

<?= $form->field($model, 'file[]')->fileInput(['multiple' => true])->hint('Доступные форматы: <strong>jpg, jpeg, png, webp, gif</strong>'); ?>



<?php

$script = <<< JS
$(document).on('click','.attachment-delete', function(e) {
    var id = $(this).attr('data-id');
    console.log('test');
    //return false;
    $.ajax({
       url: $(this).attr('href'),
       type:'POST',
       data: {id: id},
       dataType:'json',
       success: function(data) {
            if(data.success){
                common.notify(data.message,"success");
                $('tr[data-key="'+id+'"]').remove();
                //$.pjax.reload({container:'#pjax-grid-image'});
                common.removeLoader();
            }
       }
    });
    return false;
});
        
        
        
        $(document).on('click','.copper', function(e) {
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
    .modal .modal-dialog {
        width: 750px;
        margin: auto;
    }


</style>
<?php

Modal::begin([
    'options' => [
        'id' => 'cropper-modal',
        'style' => 'width:100%'
    ],
    'title' => '<h2>Cropper</h2>',
    'toggleButton' => false,
    'bodyOptions' => ['id' => 'cropper-body', 'style' => 'width:100%']
]);

echo '';

Modal::end();

?>


<?php


$searchModel = new ImageSearch();
$dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(), ['model' => $model, 'object_id' => $model->primaryKey]);


Pjax::begin([
    'dataProvider' => $dataProvider,
]);
echo panix\engine\grid\GridView::widget([
    //'id' => 'grid-images',
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'enableLayout' => false,
    //'layout'=>'{items}',
    'rowOptions' => function ($model, $index, $widget, $grid) {
        $coverClass = ($model->is_main) ? 'bg-success active sortable-column' : 'sortable-column';
        return ['class' => $coverClass];
    },
    'columns' => [
        [
            'class' => 'panix\engine\grid\sortable\Column',
            'url' => ['/admin/images/default/sortable'],
        ],
        [
            'attribute' => 'image',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center image'],
            'value' => function ($model) {
                return Html::a(Html::img($model->getUrl('100x100'), ['class' => 'img-thumbnail']), $model->getUrl(), ['class' => 'fancybox']);
            },
        ],
        [
            'attribute' => 'is_main',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return Html::radio('AttachmentsMainId', $model->is_main, [
                    'value' => $model->id,
                    'class' => 'check',
                    'data-toggle' => "tooltip",
                    'title' => Yii::t('app/default', 'IS_MAIN'),
                    'id' => 'main_image_' . $model->id
                ]);
            },
        ],
        [
            'attribute' => 'alt_title',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center'],
            'value' => function ($model) {
                return Html::textInput('attachment_image_titles[' . $model->id . ']', $model->alt_title, array('class' => 'form-control'));
            },
        ],
        [
            'class' => 'panix\engine\grid\columns\ActionColumn',
            'template' => '{resize} {settings} {delete}',
            'filter' => false,
            'buttons' => [
                /* 'resize' => function ($url, $data, $key) {
                     return Html::a(Html::icon('resize'), ['s'], array('class' => 'btn btn-sm btn-default attachment-zoom', 'data-fancybox' => 'gallery'));
                 },
                 'settings' => function ($url, $data, $key) {
                     return Html::a(Html::icon('settings'), ['/admin/images/default/edit-crop', 'id' => $data->id], array('class' => 'btn btn-sm btn-default copper'));
                 },*/
                'delete' => function ($url, $data, $key) use ($model) {
                    return Html::a(Html::icon('delete'), ['/admin/images/default/delete', 'id' => $data->id], [
                        'class' => 'btn btn-sm btn-danger attachment-delete',
                        'data-id' => $data->id,
                        //'data-object_id' => $model->id,
                        'data-pjax' => '0',
                        //'data-model' => get_class($model)
                    ]);
                },
            ]
        ]
    ],
    'filterModel' => $searchModel
]);
Pjax::end();
?>









