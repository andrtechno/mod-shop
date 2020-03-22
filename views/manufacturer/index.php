<?php
use panix\engine\Html;

?>

<?php
$memory = NULL;
$sorting = [];

foreach ($model as $item) {
    $productCount = $item->productsCount;
    if ($productCount) {

        $letter = mb_substr($item->name, 0, 1, 'utf-8');

        if ($letter != $memory) {
            $memory = $letter;
        }
        if (is_numeric($letter)) {
            $memory = '0-9';
        }
        $sorting[$memory][] = ['item' => $item, 'count' => $productCount];
    }
}
ksort($sorting);
?>
<div class="container">
    <div class="heading-gradient">
        <h1><?= $this->context->pageName; ?></h1>
    </div>
    <div class="row">
        <div class="col-12 mb-5">
            <?php foreach ($sorting as $key => $items) { ?>
                <?= Html::a(mb_strtoupper($key, 'utf-8'), ['/shop/manufacturer/index', '#' => $key], ['class' => 'h3 mr-2']); ?>
            <?php } ?>
        </div>
        <hr/>
        <?php foreach ($sorting as $key => $items) { ?>
            <div class="col-sm-12 mb-5" id="<?= $key; ?>">
                <div class="h1"><?= mb_strtoupper($key, 'utf-8'); ?></div>
                <div class="row">
                    <?php foreach ($items as $value) { ?>
                        <div class="col-sm-3"><?= Html::a($value['item']->name, $value['item']->getUrl()); ?>
                            <sup>(<?= $value['count']; ?>)</sup></div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
