<?php
use panix\engine\Html;
use panix\ext\owlcarousel\OwlCarouselWidget;

//Html::img($data->getImage('image', '100x80')->url, array('class' => '', 'alt' => $data->name))
?>

<?php if ($model) { ?>
    <h3><?= Yii::t('shop/default', 'FILTER_BY_MANUFACTURER'); ?></h3>
    <?php OwlCarouselWidget::begin([
        'containerOptions' => ['class' => 'owl-brands'],
        'options' => [
            'nav' => true,
            'margin' => 20,
            'navText' => ['', ''],
            'responsiveClass' => true,
            'responsive' => [
                0 => [
                    'items' => 1,
                    'nav' => false,
                    'dots' => true,
                    'center' => true,
                ],
                426 => [
                    'items' => 2,
                    'nav' => false
                ],
                768 => [
                    'items' => 2,
                    'nav' => false
                ],
                1024 => [
                    'items' => 6,
                    'nav' => true,
                    'dots' => false
                ]
            ]
        ]
    ]);
    foreach ($model as $data) { ?>

        <div class="text-center">
            <?php
            echo Html::a(Html::img($data->getImageUrl('image', '200x150'), ['alt' => $data->name, 'class' => 'd-inline-block img-fluid']), $data->getUrl(), ['class' => 'd-inline-block m-auto1']);
            ?>
            <h3><?=$data->name;?></h3>
        </div>

    <?php }
    OwlCarouselWidget::end();
}
?>
