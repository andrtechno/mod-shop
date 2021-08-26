<?php

use panix\engine\Html;
use panix\ext\fancybox\Fancybox;
use panix\mod\images\models\ImageSearch;
use panix\engine\widgets\Pjax;
?>
<?= Fancybox::widget(['target' => 'a.fancybox']); ?>

<?= $form->field($model, 'file[]')->fileInput(['multiple' => true]); ?>

<?php
$images = $model->getImages();
?>


<?php

$script = <<< JS
$('.attachment-delete').on('click', function(e) {
        var id = $(this).attr('data-id');
    $.ajax({
       url: $(this).attr('href'),
       type:'POST',
       data: {id: id},
       success: function(data) {
                                            if(data.status == "success"){
                                                common.notify(data.message,"success");
                                                $("#image-"+id).remove();
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
        $('#test').html(data);
       }
    });
        return false;
});
JS;
$this->registerJs($script); //$position
?>


<?php


        $searchModel = new ImageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams(),['model'=>$model]);


Pjax::begin([
    'id' => 'pjax-container',
        //'enablePushState' => false,
        //  'linkSelector' => 'a:not(.linkTarget)'
]);
echo panix\engine\grid\GridView::widget([
    'tableOptions' => ['class' => 'table table-striped'],
    'dataProvider' => $dataProvider,
    'rowOptions' => ['class' => 'sortable-column'],
    'enableLayout'=>false,
    //'layout'=>'{items}',
    'columns' => [
        [
            'attribute' => 'image',
            'format' => 'raw',
            'contentOptions' => ['class' => 'text-center image'],
            'value' => function($model) {
                return Html::a(Html::img($model->getUrl('100x100'), ['class' => '']), $model->getUrl(), ['class' => 'img-thumbnail fancybox']);
            },
        ],
        'alt_title'
    ],
    'filterModel'=>$searchModel
]);
Pjax::end();

?>












<div class="attachments2">
    <?php if($images){ ?>
    <table class="table table-striped">
        <tr>
            <th class="text-center">Изображение</th>
            <th class="text-center">Главное</th>
            <th class="text-center">Alt-тег</th>
            <th class="text-center">Опции</th>
        </tr>
        <?php
        foreach ($images as $img) {
            $coverClass = ($img->is_main) ? 'bg-success active' : '';

            ?>
            <tr id="image-<?=$img->id?>" class="<?= $coverClass ?>">
                <td class="text-center"><?= Html::a(Html::img($img->getUrl('100x100'), ['class' => '']), $img->getUrl(), ['class' => 'img-thumbnail fancybox']); ?></td>
                <td class="text-center">
                    <?=
                    Html::radio('AttachmentsMainId', $img->is_main, array(
                        'value' => $img->id,
                        'class' => 'check',
                        'data-toggle' => "tooltip",
                        'title' => Yii::t('app/default', 'IS_MAIN'),
                        'id' => 'main_image_' . $img->id
                    ));
                    ?></td>
                <td><?= Html::input('text', 'attachment_image_titles[' . $img->id . ']', $img->alt_title, array('class' => 'form-control', 'placeholder' => $img->getAttributeLabel('alt_title'))); ?></td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <?= Html::a(Html::icon('resize'), $img->getUrl(), array('class' => 'btn btn-default attachment-zoom', 'data-fancybox' => 'gallery')); ?>
                        <?= Html::a(Html::icon('settings'), ['/images/edit-crop', 'id' => $img->id], array('class' => 'btn btn-default copper')); ?>
                        <?= Html::a(Html::icon('delete'), ['/images/default/delete', 'id' => $img->id], array('class' => 'btn btn-danger attachment-delete', 'data-id' => $img->id)); ?>
                    </div>
                </td>
            </tr>
        <?php } ?>
    </table>
    <?php } ?>
</div>

