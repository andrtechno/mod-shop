<?php
use panix\engine\Html;

?>
<?php if ($model) { ?>
    <?php foreach ($model as $data) { ?>
        <div class="autocomplete-item ">
            <div class="row">
                <div class="col-sm-2">
                    <div class="autocomplete-img text-center">
                        <?= Html::img($data->getMainImage('50x50')->url, ['alt' => $data->name, 'class' => 'img-thumbnail']); ?>
                    </div>
                </div>
                <div class="col-sm-10">
                    <div class="autocomplete-info">
                        <div><?= Html::a($data->name, $data->getUrl(), []); ?></div>
                        <div><?= $data->getFrontPrice(); ?> <?= Yii::$app->currency->active['symbol'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
    <?= Html::a('Полный результат', ['/shop/search/index', 'q' => $q], ['class' => 'btn btn-primary']); ?>

<?php } ?>

