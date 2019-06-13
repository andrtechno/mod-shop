<?php
use panix\engine\CMS;

//API https://developers.google.com/youtube/player_parameters?hl=ru
?>

<div class="embed-responsive embed-responsive-16by9">
    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?= CMS::parse_yturl($model->video); ?>?autoplay=0&controls=1&rel=0" allowfullscreen></iframe>
</div>
