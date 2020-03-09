<?php
use panix\engine\Html;

?>

<?php
$memory = NULL;
$sorting = [];

foreach ($model as $item) {
    $letter = mb_substr($item->name, 0, 1, 'utf-8');

    if ($letter != $memory) {
        $memory = $letter;
    }
	if (is_numeric($letter)) {
        $memory = '0-9';
    }
    $sorting[$memory][] = $item;
}
ksort($sorting);
?>
<div class="container">
    <div class="heading-gradient">
        <h1><?= $this->context->pageName; ?></h1>
    </div>
    <div class="row">
        <?php foreach ($sorting as $key => $items) { ?>
            <div class="col-sm-3">
                <h3><?= mb_strtoupper($key, 'utf-8'); ?></h3>
                <?php foreach ($items as $value) { ?>
                    <div class=""><?= Html::a($value->name, $value->getUrl()); ?> <?= $value->productsCount; ?></div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
