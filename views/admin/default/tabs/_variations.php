<?php
use panix\engine\Html;
use yii\helpers\ArrayHelper;
\panix\mod\shop\assets\admin\VariationsAsset::register($this);
#Yii::app()->getClientScript()->registerScriptFile($this->module->assetsUrl . '/admin/products.variations.js', CClientScript::POS_END);
?>

<div class="variants">
    <div class="form-group">
        <div class="col-sm-4"><label class="control-label">Добавить атрибут</label></div>
        <div class="col-sm-8">     
            <?php
            if ($model->type) {
                $attributes = $model->type->shopConfigurableAttributes;

                echo Html::dropDownList(
                    'variantAttribute',
                    null,
                   ArrayHelper::map($attributes, 'id', 'title')
                );
            }
            ?>
            <a href="javascript:void(0)" id="addAttribute" class="btn btn-success"><?= Yii::t('app', 'CREATE', 0) ?></a>
        </div>
        <div class="clearfix"></div>
    </div>




    <div id="variantsData">
        <?php
        foreach ($model->processVariants() as $row) {
            $this->renderPartial('variants/_table', array(
                'attribute' => $row['attribute'],
                'options' => $row['options']
            ));
        }
        ?>
    </div>
</div>