<?php
/**
 * @var $data \panix\mod\shop\models\Attribute
 */
?>

<table class="table table-striped" id="attributes-list">
    <?php foreach ($data as $title => $value) { ?>
        <tr>
            <td><strong><?= $title ?>:</strong></td>
            <td><?= $value ?></td>
        </tr>
    <?php } ?>
</table>
