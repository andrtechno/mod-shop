<?php
use panix\engine\Html;
use yii\helpers\ArrayHelper;
?>

<table class="variantsTable table table-bordered" id="variantAttribute<?php echo $attribute->id ?>">
    <thead>
        <tr>
            <td colspan="6">
                <h4 class="d-inline"><?php echo Html::encode($attribute->title); ?>
                <?php
                echo Html::a(Html::icon('add').' '.Yii::t('shop/admin','ADD_OPTION'), '#', array(
                    'rel' => $attribute->id,
                    'class' => 'btn btn-sm btn-success',
                    'onclick' => 'js: return addNewOption($(this));',
                    'data-name' => $attribute->getIdByName(),
                ));
                ?>
                </h4>
            </td>
        </tr>
        <tr>
            <th>Значение</th>
            <th><?=Yii::t('shop/Product','PRICE')?> (<?=Yii::$app->currency->main['iso'] ?>)</th>
            <th>Валюта</th>
            <th>Тип цены</th>
            <th><?=Yii::t('shop/Product','SKU')?></th>
            <th class="text-center">
                <?php
                echo Html::a('<i class="icon-add"></i>', '#', array(
                    'rel' => '#variantAttribute' . $attribute->id,
                    'class' => 'plusOne btn btn-sm btn-success',
                    'onclick' => 'js: return cloneVariantRow($(this));'
                ));
                ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php if (!isset($options)) { ?>
            <tr>
                <td>
                    <?php echo Html::dropDownList('variants[' . $attribute->id . '][option_id][]', null, ArrayHelper::map($attribute->options, 'id', 'value'), ['class' => 'options_list select custom-select w-auto']); ?>
                </td>
                <td>
                    <input class="form-control" type="text" name="variants[<?php echo $attribute->id ?>][price][]">
                </td>
                <td>
                    <?= Html::dropDownList('variants[' . $attribute->id . '][currency][]', null, ArrayHelper::map(\panix\mod\shop\models\Currency::find()->published()->andWhere(['!=', 'id', Yii::$app->currency->main['id']])->all(), 'id', 'iso'), ['class' => 'custom-select w-auto','prompt'=>'---']); ?>
                </td>
                <td>
                    <?= Html::dropDownList('variants[' . $attribute->id . '][price_type][]', null, array(0 => Yii::t('shop/admin','VARIANTS_PRICE_FIX'), 1 => Yii::t('shop/admin','VARIANTS_PRICE_PERCENT')), ['class' => 'custom-select w-auto']); ?>
                </td>
                <td>
                    <input class="form-control" type="text" name="variants[<?php echo $attribute->id ?>][sku][]" />
                </td>
                <td class="text-center">
                    <a href="#" class="btn btn-sm btn-danger" onclick="return deleteVariantRow($(this));"><i class="icon-delete"></i></a>
                </td>
            </tr>
        <?php } ?>
        <?php
        if (isset($options)) {
            foreach ($options as $o) {
                ?>
                <tr>
                    <td>
                        <?php echo Html::dropDownList('variants[' . $attribute->id . '][option_id][]', $o->option->id, ArrayHelper::map($attribute->options, 'id', 'value'), ['class' => 'options_list custom-select w-auto']); ?>
                    </td>
                    <td>
                        <input class="form-control" type="text" name="variants[<?php echo $attribute->id ?>][price][]" value="<?php echo $o->price ?>">
                    </td>
                    <td>
                        <?= Html::dropDownList('variants[' . $attribute->id . '][currency][]', $o->currency_id, ArrayHelper::map(\panix\mod\shop\models\Currency::find()->published()->andWhere(['!=', 'id', Yii::$app->currency->main['id']])->all(), 'id', 'iso'), ['class' => 'custom-select w-auto','prompt'=>'---']); ?>
                    </td>
                    <td>
                        <?php echo Html::dropDownList('variants[' . $attribute->id . '][price_type][]', $o->price_type, array(0 => Yii::t('shop/admin','VARIANTS_PRICE_FIX'), 1 => Yii::t('shop/admin','VARIANTS_PRICE_PERCENT')), ['class' => 'custom-select w-auto']); ?>
                    </td>
                    <td>
                        <input class="form-control" type="text" name="variants[<?php echo $attribute->id ?>][sku][]" value="<?php echo $o->sku ?>">
                    </td>
                    <td class="text-center">
                        <a href="#" class="btn btn-sm btn-danger" onclick="return deleteVariantRow($(this));"><i class="icon-delete"></i></a>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
    </tbody>
</table>