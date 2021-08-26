<?php

?>

<button class="btn btn-primary btn-lg btn-block" type="button" data-toggle="collapse" data-target="#catalog-container" aria-expanded="false" aria-controls="catalog-container">
    Каталог продукции
</button>
<div class="collapse in" id="catalog-container">
    <?php echo $this->context->recursive($result); ?>
</div>