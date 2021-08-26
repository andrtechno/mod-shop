<?php

use panix\engine\Html;
use panix\engine\CMS;
use panix\mod\shop\models\Category;

// \yii\helpers\VarDumper::dump(Category::findOne(44),100,true);
// die;
$root = Category::findOne(1);

$categories = $root->children()->all();
?>
<div class="row">
    <div class="container">
        <div class="row">
            <?php
            $totalProducts = 0;
            foreach ($categories as $cat) {
                $totalProducts = $cat->countItems;
                ?>

                <div class="col-md-6 col-sm-6 text-left">
                    <?php
                    echo Html::a($cat->name, $cat->getUrl(), ['class' => 'thumbnail']);
                    echo Html::tag('sup', $totalProducts, []);
                    ?>
                </div>
                <div class="col-md-6 col-sm-6 text-left">
                    <b><?= Html::a($cat->name, $cat->getUrl()) ?></b>
                    <ul class="list-unstyled">
                        <?php
                        foreach ($cat->children()->published()->all() as $subcat) {
                            //  $totalProducts +=$subcat->countProducts;
                            ?>
                            <li>
                                <?= Html::a($subcat->name, $subcat->getUrl()); ?>
                                <?= Html::tag('sup', $subcat->countItems, []); ?>
                            </li>
                        <?php } ?>
                    </ul>


                </div>
            <?php } ?>
        </div>
    </div>
</div>
