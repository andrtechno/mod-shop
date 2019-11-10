<?php
use panix\engine\Html;
use panix\ext\owlcarousel\OwlCarouselWidget;

//Html::img($data->getImage('image', '100x80')->url, array('class' => '', 'alt' => $data->name))
?>

<?php if ($model) { ?>
    <h3><?= Yii::t('shop/default', 'BLOCK_MANUFACTURER_TITLE'); ?></h3>
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
                    'items' => 5,
                    'nav' => true,
                    'dots' => false
                ]
            ]
        ]
    ]);
    foreach ($model as $data) { ?>
        <div class="text-center">
            <div class="d-flex align-items-center" style="height: 80px;">

                <?= Html::a(Html::img($data->getImageUrl('image', '150x80'), [
                    'alt' => $data->name,
                    'class' => 'd-inline-block img-fluid'
                ]), $data->getUrl(), ['class' => 'd-inline-block m-auto']); ?>
            </div>
            <div><?= Html::a($data->name, $data->getUrl(), ['class' => 'h3 d-block mt-3']); ?></div>
        </div>

    <?php }
    OwlCarouselWidget::end();
}
?>
