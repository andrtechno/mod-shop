<?php
use panix\ext\owlcarousel\OwlCarouselWidget;

OwlCarouselWidget::begin([
    'container' => 'div',
    'containerOptions' => [
        // 'id' => 'container-id',
         'class' => '_view_grid'
    ],
    'pluginOptions' => [
        'autoplay' => false,
        'autoplayTimeout' => 3000,
        'items' => 3,
        'loop' => false,
        'margin' => 0,
        'responsiveClass' => true,
        'responsive' => [
            0 => [
                'items' => 1,
                'nav' => false,
                'dots'=>true
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
                'dots'=>true
            ]
        ]
    ]
]);
?>
<?php foreach ($model->relatedProducts as $data) { ?>
    <div class="item">
        <?php echo $this->render('/category/_view_grid', [
            'model' => $data
        ]); ?>
    </div>
    <div class="item">
        <?php echo $this->render('/category/_view_grid', [
            'model' => $data
        ]); ?>
    </div>
    <div class="item">
        <?php echo $this->render('/category/_view_grid', [
            'model' => $data
        ]); ?>
    </div>
    <div class="item">
        <?php echo $this->render('/category/_view_grid', [
            'model' => $data
        ]); ?>
    </div>
<?php } ?>

<?php OwlCarouselWidget::end(); ?>