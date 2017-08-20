<?php
use yii\helpers\Html;

?>
<div id="catalog-left-nav">
    <?= Html::a('Каталог продукции', array('/shop'), array('class' => 'btn btn-danger btn-lg btn-block catalog-title')); ?>
    <div class="test">
        <?php
        echo $this->context->recursive($result['items']);
        ?>
    </div>
</div>



