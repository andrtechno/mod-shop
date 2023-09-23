<?php
use panix\engine\Html;
use yii\helpers\ArrayHelper;

\panix\mod\shop\bundles\admin\VariationsAsset::register($this);
#Yii::app()->getClientScript()->registerScriptFile($this->module->assetsUrl . '/admin/products.variations.js', CClientScript::POS_END);
?>

<div class="variants">
    <div class="form-group row">
        <label class="col-form-label col-sm-4"><? Yii::t('shop/admin','CREATE_ATTRIBUTE'); ?></label>
        <div class="col-sm-8">

            <div class="input-group">
                <?php
                if ($model->type) {
                    $attributes = $model->type->shopConfigurableAttributes;

                    echo Html::dropDownList('variantAttribute', null,
                        ArrayHelper::map($attributes, 'id', 'title'),
                        ['id' => 'variantAttribute', 'class' => 'custom-select', 'style' => 'width:auto;']
                    );
                }
                ?>
                <div class="input-group-prepend">
                    <a href="#" id="addAttribute" class="btn btn-success"><?= Yii::t('app/default', 'CREATE', 0) ?></a>
                </div>
            </div>
        </div>

    </div>


    <div id="variantsData">
        <?php
        foreach ($model->processVariants() as $row) {
            echo $this->render('variants/_table', array(
                'attribute' => $row['attribute'],
                'options' => $row['options']
            ));
        }
        ?>
    </div>
</div>
