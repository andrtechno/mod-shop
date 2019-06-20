<?php foreach ($model->sets as $set) { ?>
<div>
    <h3>Вместе дешевле</h3>
    <?php
    //print_r($set->products);

    foreach ($set->products as $set_product) { ?>


        <?= $set_product->discount; ?>

        <?= $set_product->product; ?>
        <br><br>
    <?php } ?>
</div>
<?php } ?>