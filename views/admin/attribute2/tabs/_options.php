<?php

use panix\mod\shop\models\translate\AttributeOptionTranslate;
use panix\engine\Html;
use panix\engine\widgets\Pjax;
use panix\mod\shop\models\AttributeOption;

\panix\mod\shop\bundles\admin\AttributeAsset::register($this);
/**
 * @var $this \yii\web\View
 * @var $model \panix\mod\shop\models\Attribute
 */

?>
<?php if ($model->sort) { ?>
    <div class="alert alert-warning">
        <?= $model::t('ALERT_ENABLE_SORT', $model::sortList()[$model->sort]); ?>
    </div>
<?php } ?>

<?php


$columns = [];
if (!$model->sort) {
    $columns[] = [
        'class' => 'panix\engine\grid\sortable\Column',
        'url' => ['/admin/shop/attribute/sortableOptions']
    ];
}
$data = [];
$data2 = [];
$test = [];
/*$languages = Yii::$app->languageManager->languages;
foreach ($model->options as $k => $o) {

    foreach ($languages as $k => $l) {
        $value = ($k == 'ru') ? 'value' : 'value_' . $l->code;
        $data2['name_' . $l->code] = Html::textInput('options[' . $o->id . '][]', $o->{$value}, ['class' => 'form-control input-lang', 'style' => 'background-image:url(/uploads/language/' . $k . '.png);']);
        $columns[$l->code] = [
            'header' => $l->name,
            'attribute' => 'name_' . $l->code,
            'format' => 'raw',
        ];
    }


    $data2['products'] = Html::a($o->productsCount, ['/admin/shop/product/index', 'ProductSearch[eav][' . $model->name . ']' => $o->id], ['target' => '_blank','data-pjax'=>0]);
    $data[$o->id] = (array)$data2;
    // }


}*/


$sortAttributes[] = 'name';
$columns[] = [
    'header' => Yii::t('shop/admin', 'PRODUCT_COUNT'),
    'format' => 'raw',
    'contentOptions' => ['class' => 'text-center'],
    'value' => function ($model) {
        return Html::textInput('asd', $model->value, ['class' => 'form-control']);
    }
];
$columns[] = [
    'header' => Yii::t('shop/admin', 'PRODUCT_COUNT'),
    'format' => 'raw',
    'contentOptions' => ['class' => 'text-center'],
    'value' => function ($model) {
        return Html::textInput('asd', $model->value_uk, ['class' => 'form-control']);
    }
];
$columns[] = [
    'header' => Yii::t('shop/admin', 'PRODUCT_COUNT'),
    'format' => 'raw',
    'contentOptions' => ['class' => 'text-center'],
    'value' => function ($model) {
        return $model->productsCount;
    }
];
$columns[] = [
    'header' => Yii::t('app/default', 'OPTIONS'),
    'attribute' => 'delete',
    'format' => 'raw',
    'contentOptions' => ['class' => 'text-center'],
    'filterOptions' => ['class' => 'text-center'],
    'filter' => Html::button(Html::icon('add'), ['data-toggle' => 'modal', 'data-target' => '#optionModal', 'class' => 'btn btn-sm btn-success', 'title' => 'Добавить опцию']),
    'value' => function ($model) {
        return Html::a(Html::icon('delete'), ['delete-option', 'id' => $model->id], ['data-pjax' => 0,'class' => 'btn btn-sm btn-outline-danger delete-option']);
    }
    //'filter' => Html::a(Html::icon('add'), '#', ['title' => 'Добавить опцию', 'class' => 'btn btn-sm btn-success', 'id' => 'add-option-attribute'])
];


$modelOpt = new \panix\mod\shop\models\search\AttributeOptionSearch();
$dataProvider = $modelOpt->search(Yii::$app->request->getQueryParams());


Pjax::begin([
    'id' => 'pjax-option',
    //'enablePushState' => false,
    //  'linkSelector' => 'a:not(.linkTarget)'
]);
echo panix\engine\grid\GridView::widget([
    'tableOptions' => ['class' => 'table table-striped optionsEditTable'],
    'dataProvider' => $dataProvider,
    'rowOptions' => ['class' => 'sortable-column'],
    'enableLayout' => false,
    'layout' => '{items}{pager}',
    'columns' => $columns,
    'filterModel' => true
]);
Pjax::end();
?>


<?php $this->registerJs("
    $(document).on('beforeValidate', '#option-form', function (event, messages, deferreds) {
        //console.log('beforeValidate',messages);
        $(this).find('button[type=\"submit\"]').attr('disabled','disabled');
    }).on('afterValidate', '#option-form', function (event, messages, errorAttributes) {
        //console.log('afterValidate');
        var countErrors = 0;
        if (errorAttributes.length) {
            if(!countErrors){
                $(this).find('button[type=\"submit\"]').removeAttr('disabled');
            }else{
                //$(this).find('button[type=\"submit\"]').attr('disabled','disabled');
            }
        }else{
            //$(this).find('#cart-submit').removeAttr('disabled');
        }
    }).on('beforeSubmit', '#option-form', function (event) {
        //console.log('beforeSubmit');
        var form = $(this);
        $.ajax({
            type:form.attr('method'),
            url:form.attr('action'),
            data: form.serialize(),
            dataType:'json',
            success:function(data){
                $('#optionModal').modal('hide');
                $.pjax.reload('#pjax-option', {timeout: false});
                form.find('button[type=\"submit\"]').removeAttr('disabled');
            }
        });
        return false;
    });
    $(document).on('click', '.delete-option', function(){
        var _this = $(this);
        $.ajax({
            type: 'POST',
            url: _this.attr('href'),
            dataType: 'json',
            success: function(data) {
                if(data.success){
                    _this.closest('tr').remove();
                    common.notify(data.message,'success');
                }
            }
        });
        return false;
    });
");
