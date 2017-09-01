    <?php
    use yii\helpers\Html;
    use yii\helpers\HtmlPurifier;
    ?>
     
    <div class="news-item">
        <h2><?= Html::a(Html::encode($model->name),$model->getUrl()) ?></h2>    
        <?= HtmlPurifier::process($model->name) ?>    
    </div>