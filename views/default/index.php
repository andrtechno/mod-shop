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
    <?php
    $totalProducts = 0;
    foreach ($categories as $cat) {
        //  \yii\helpers\VarDumper::dump($cat,100,true);
        //  die;
        $totalProducts = $cat->countItems;
        ?>
        <div class="col-sm-3 col-md-4">
            <div class="row">
                <div class="col-md-6 col-sm-6 text-left">
                    <?php
                    echo Html::a($cat->name . '<sub>' . $totalProducts . '</sub>', $cat->getUrl(), ['class' => 'thumbnail']);
                    // if ($cat->getImageUrl('image', 'categories', '235x320')) {
                    //     $imgSource = $cat->getImageUrl('image', 'categories', '235x320'); //
                    // } else {
                    //     $imgSource = CMS::placeholderUrl(array('size'=>'235x320'));
                    //}
                    // echo Html::a(Html::img($imgSource, $cat->name, array('class' => 'img-responsive', 'height' => 240)), $cat->getUrl(), array('class' => 'thumbnail'));
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
            </div>
        </div>
    <?php } ?>

</div>
