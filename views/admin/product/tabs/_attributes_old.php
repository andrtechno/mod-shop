<?php

use panix\engine\Html;
use panix\mod\shop\models\Attribute;

//$chosen = array(); // Array of ids to enable chosen
$attributes = (isset($model->type->shopAttributes)) ? $model->type->shopAttributes : array();
?>
<div clsas="container-fluid">
    <div class="row ml-0z mr-0z">
        <?php
        if (empty($attributes))
            echo Yii::t('shop/admin', 'Список свойств пустой');
        else {

            foreach ($attributes as $a) {
                if ($a->group) {
                    $result[$a->group->name][] = $a;
                } else {
                    $result['Без группы'][] = $a;
                }

            }

            foreach ($result as $group_name => $attributes) {
                echo '<div class="col-sm-12 col-md-6 col-lg-6 col-xl-4"><h2 class="text-center mt-3">' . $group_name . '</h2>';
                foreach ($attributes as $a) {
                    // Repopulate data from POST if exists
                    if (isset($_POST['Attribute'][$a->name]))
                        $value = $_POST['Attribute'][$a->name];
                    else
                        $value = $model->getEavAttribute($a->name);

                    $a->required ? $required = ' <span class="required">*</span>' : $required = null;

                    if ($a->type == Attribute::TYPE_DROPDOWN) {
                        //   $chosen[] = $a->getIdByName();

                        $addOptionLink = Html::a(Html::icon('add'), '#', array(
                            'rel' => $a->id,
                            'data-name' => $a->getIdByName(),
                            'onclick' => 'js: return addNewOption($(this));',
                            'class' => 'btn btn-success btn-sm pull-left',
                            'title' => Yii::t('shop/admin', 'Создать опцию')
                        ));
                    } else
                        $addOptionLink = null;

                    // print_r($a);


                    echo Html::beginTag('div', array('class' => 'form-group row'));
                    echo '<div class="col-sm-4">' . Html::label($a->title . $required, $a->name, array('class' => $a->required ? 'required' : 'control-label')) . '</div>';
                    echo '<div class="col-sm-8 rowInput eavInput">' . $a->renderField($value) . Html::error($model, 'name', ['class' => 'text-danger']) . $addOptionLink . '</div>';
                    echo Html::endTag('div');
                }
                echo '</div>';
            }


        }
        ?>
    </div>
</div>
