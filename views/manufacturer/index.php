<?php
use panix\engine\Html;


?>


<?php foreach ($model as $data) { ?>
    <div class=""><?= Html::a($data->name, $data->getUrl()); ?></div>
<? } ?>

