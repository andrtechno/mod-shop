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
            'margin' => 5,
            'responsiveClass' => true,
            'responsive' => [
                0 => [
                    'items' => 1,
                    'nav' => false,
                    'dots' => true
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
                    'items' => 4,
                    'nav' => true,
                    'dots' => true
                ]
            ]
        ]
    ]);
    foreach ($model as $data) { ?>

        <div class="d-flex align-items-center">
            <div>
                <?php
                echo Html::a(Html::img($data->getImageUrl('image', '100x80'), ['alt' => $data->name, 'class' => 'img-fluid']), $data->getUrl(), ['class' => 'd-block m-auto1','style'=>'height:100px']);
                ?>
                <div class="text-center h5">
                    <?= $data->name; ?>
                </div>
            </div>
        </div>

    <?php }
    OwlCarouselWidget::end();
}
?>
