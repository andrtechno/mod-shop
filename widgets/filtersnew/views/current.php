<?php
use yii\helpers\Html;
use yii\widgets\Menu;

?>
<div class="card" id="filter-current">
    <div class="card-header">
        <h5><?= Yii::t('shop/default', 'FILTER_CURRENT') ?></h5>
    </div>
    <div class="card-body">
        <?php
        echo Menu::widget([
            'items' => $active,
        ]);
        if($this->context->model) {
            echo Html::a(Yii::t('shop/default', 'RESET_FILTERS_BTN'), $this->context->model->getUrl(), array('class' => 'btn btn-secondary'));
        }
        ?>
    </div>
    <div class="card-footer">
    </div>
</div>
