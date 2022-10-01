<?php

use panix\engine\Html;


?>
<div class="container">

    <div class="row mt-4">
        <div class="col-12 mb-5">
            <?php foreach ($items as $key => $item) { ?>
                <?= Html::a(mb_strtoupper($key, 'utf-8'), ['/shop/brand/index', '#' => $key], ['class' => 'mr-2 brand-list-name']); ?>
            <?php } ?>
        </div>
        <hr/>
        <?php foreach ($items as $key => $brands) { ?>
            <div class="col-sm-12 mb-5" id="<?= $key; ?>">
                <div class="h1"><?= mb_strtoupper($key, 'utf-8'); ?></div>
                <div class="row">
                    <?php foreach ($brands as $value) { ?>
                        <div class="col-sm-3"><?= Html::a($value['item']->name, $value['item']->getUrl()); ?>
                            <sup>(<?= $value['count']; ?>)</sup></div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
