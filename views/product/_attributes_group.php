<?php
use yii\helpers\Html;

/**
 * @var $groups \panix\mod\shop\models\AttributeGroup
 * @var $this \yii\web\View
 */

?>
<div class="row">
    <?php foreach ($groups as $group_name => $group) { ?>
        <div class="col-md-12 col-lg-6">
            <table class="table table-sm table-attributes">
                <thead>
                <tr>
                    <th colspan="2"><h5><?= Html::encode($group_name) ?></h5></th>

                </tr>
                </thead>
                <?php foreach ($group as $item) { ?>
                    <tr>
                        <td>
                            <span><?= Html::encode($item['name']) ?>
                                <?php if (!empty($item['hint'])) { ?>
                                    <a href="javascript:void(0)" title="<?= Html::encode($item['name']) ?>"
                                       class="attribute-popover<?= $item['id']; ?>"><i class="icon-info"></i></a>
                                <?php $this->registerJsScript('attribute-popover-' . $item['id'], "$('.attribute-popover" . $item['id'] . "').popover({html: true,trigger: 'focus',content: function () {return $('#attribute-popover-" . $item['id'] . "').html();}});"); ?>



                                    <div id="attribute-popover-<?= $item['id']; ?>" class="d-none">
                                        <?= $item['hint']; ?>
                                    </div>
                                <?php } ?>
                            </span>
                        </td>
                        <td class="text-right">
                            <span class="font-weight-bold"><?= Html::a(Html::encode($item['value']),[]); ?></span>
                        </td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php } ?>
</div>
