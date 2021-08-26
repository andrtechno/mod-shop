<?php
use panix\engine\Html;
\app\web\themes\dashboard\AdminAsset::register($this);
\panix\engine\widgets\PjaxAsset::register($this);
$this->registerJs("
    function checkAllDuplicateAttributes(el){
        if($(el).prev().attr('checked')){
            $('#duplicate_products_dialog form input').attr('checked', false);
            $(el).prev().attr('checked', false);
        }else{
            $('#duplicate_products_dialog form input').attr('checked', true);
            $(el).prev().attr('checked', true);
        }
    }
");
?>
<div class="p-3">
    <?= Html::beginForm('', 'POST', []); ?>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <?= Html::checkbox('copy[]', true, ['value' => 'images', 'id' => 'images', 'class' => 'custom-control-input']); ?>
            <?= Html::label(Yii::t('shop/Product', 'TAB_IMG'), 'images', ['class' => 'custom-control-label']); ?>
        </div>
    </div>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <?= Html::checkbox('copy[]', true, ['value' => 'variants', 'id' => 'variants', 'class' => 'custom-control-input']); ?>
            <?= Html::label(Yii::t('shop/Product', 'TAB_VARIANTS'), 'variants', ['class' => 'custom-control-label']); ?>
        </div>
    </div>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <?= Html::checkbox('copy[]', true, ['value' => 'related', 'id' => 'related', 'class' => 'custom-control-input']); ?>
            <?= Html::label(Yii::t('shop/Product', 'TAB_REL'), 'related', ['class' => 'custom-control-label']); ?>
        </div>
    </div>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <?= Html::checkbox('copy[]', true, ['value' => 'attributes', 'id' => 'attributes', 'class' => 'custom-control-input']); ?>
            <?= Html::label(Yii::t('shop/Product', 'Характеристики'), 'attributes', ['class' => 'custom-control-label']); ?>
        </div>
    </div>
    <div class="form-group">
        <div class="custom-control custom-checkbox">
            <?= Html::checkbox('check_all', true, ['value' => 1, 'id' => 's123', 'class' => 'custom-control-input']); ?>
            <?= Html::label(Yii::t('shop/admin', 'Отметить все'), 's123', ['onClick' => 'return checkAllDuplicateAttributes(this);', 'class' => 'custom-control-label']); ?>
        </div>
    </div>
    <?= Html::endForm(); ?>

</div>