<?php
use panix\engine\Html;

?>
<div class="media mb-3">

    <?php
    echo Html::a(Html::img($model->getMainImage('64x64')->url, ['alt' => $model->name, 'class' => 'mr-3']), $model->getUrl(), ['data-pjax' => 0]);
    ?>
    <div class="media-body">
        <h5 class="mt-0"><?= Html::a(Html::encode($model->name), $model->getUrl(), ['class' => '', 'data-pjax' => 0]) ?></h5>
        Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin. Cras purus odio, vestibulum in vulputate at, tempus viverra turpis. Fusce condimentum nunc ac nisi vulputate fringilla. Donec lacinia congue felis in faucibus.
    </div>
</div>
