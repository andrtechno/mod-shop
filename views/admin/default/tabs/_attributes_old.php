

<?php
use panix\engine\Html;
use panix\mod\shop\models\Attribute;


$chosen = array(); // Array of ids to enable chosen
$attributes = (isset($model->type->shopAttributes))?$model->type->shopAttributes:array();

if (empty($attributes))
    echo Yii::t('shop/admin', 'Список свойств пустой');
else {
    foreach ($attributes as $a) {
        // Repopulate data from POST if exists
        if (isset($_POST['Attribute'][$a->name]))
            $value = $_POST['Attribute'][$a->name];
        else
            $value = $model->getEavAttribute($a->name);

        $a->required ? $required = ' <span class="required">*</span>' : $required = null;

        if ($a->type == Attribute::TYPE_DROPDOWN) {
            $chosen[] = $a->getIdByName();

            $addOptionLink = Html::a(Html::icon('add'), '#', array(
                        'rel' => $a->id,
                        'data-name' => $a->getIdByName(),
                        'onclick' => 'js: return addNewOption($(this));',
                        'class' => 'btn btn-success btn-sm pull-right',
                        'title' => Yii::t('shop/admin', 'Создать опцию')
            ));
        } else
            $addOptionLink = null;

        echo Html::beginTag('div', array('class' => 'form-group'));
        echo '<div class="col-sm-4">' . Html::label($a->title . $required, $a->name, array('class' => $a->required ? 'required' : 'control-label')) . '</div>';
        echo '<div class="col-sm-8 rowInput eavInput">' . $a->renderField($value)  . $addOptionLink.'</div>';
        echo Html::endTag('div');

    }
    //   }
}