<?php

use yii\helpers\Html;
?>

<table class="table table-striped" id="attributes-list">

    <?php foreach ($data as $title => $value) { ?>
        <tr>
            <td><?= $title ?>:</td>
            <td><?= $value ?></td>
        </tr>
    <?php } ?>
</table>
