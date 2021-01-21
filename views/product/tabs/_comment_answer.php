<div class="answer mt-3">
    <div class="row">
        <div class="col-4 col-lg-2">
            <div class="review-info">
                <div class="review-name">CHIKA</div>
                <div class="review-date"><?= \panix\engine\CMS::date($model->created_at, false); ?></div>
                <?php if ($model->user_id) { ?>
                    <?php if ($model->user) { ?>
                        <ul class="social clearfix">
                            <?php if ($model->user->facebook_url) { ?>
                                <li><a class="fb" href="<?= $model->user->facebook_url; ?>"></a></li>
                            <?php } ?>
                            <?php if ($model->user->instagram_url) { ?>
                                <li><a class="inst" href="<?= $model->user->instagram_url; ?>"></a></li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
        <div class="col-8 col-lg-10">
            <p class="review-txt"><?= $model->text; ?></p>
        </div>
    </div>
</div>