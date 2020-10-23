<div class="answer mt-3">
    <div class="row">
        <div class="col-4 col-lg-2">
            <div class="review-info">
                <div class="review-name">CHIKA</div>
                <div class="review-date"><?= \panix\engine\CMS::date($model->created_at, false); ?></div>
                <?php if($model->user_id){ ?>
                    <ul class="social clearfix">
                        <li><a class="fb" href="/"></a></li>
                        <li><a class="inst" href="/"></a></li>
                    </ul>
                <?php } ?>
            </div>
        </div>
        <div class="col-8 col-lg-10">
            <p class="review-txt"><?= $model->text; ?></p>
        </div>
    </div>
</div>