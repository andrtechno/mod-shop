<?php

use yii\helpers\Html;
?>


<?= $form->field($model, 'file[]')->fileInput(['multiple' => true]); ?>

<?php

$images = $model->getImages();
foreach ($images as $img) {
    //retun url to full image
    // echo Html::img($img->getUrl());
    //return url to proportionally resized image by width
    // echo Html::img($img->getUrl('300x'));
    //return url to proportionally resized image by height
    //echo Html::img($img->getUrl('x100'));
    //return url to resized and cropped (center) image by width and height
    //echo Html::img($img->getUrl('200x300'));
}
echo Html::img($model->getImage()->getUrl('50x50'));
?>