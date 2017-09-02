<?php
use yii\helpers\Html;
use yii\widgets\Menu;
?>
<div class="panel panel-default" id="filter-current">

    <div class="panel-heading">
        <div class="panel-title"><?= Yii::t('shop/default', 'FILTER_CURRENT') ?></div>
    </div>
    <div class="panel-body">
        <?php
      /*  $this->widget('zii.widgets.CMenu', array(
            'htmlOptions' => array('class' => 'current-filter-list'),
            'items' => $active
        ));*/
        echo Menu::widget([
    'items' => $active,
]);
        echo Html::a(Yii::t('shop/default', 'RESET_FILTERS_BTN'), $this->context->model->getUrl(), array('class' => 'btn btn-xs btn-default'));
        ?>
    </div>

</div>
