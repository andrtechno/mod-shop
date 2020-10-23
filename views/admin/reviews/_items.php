<?php
if (Yii::$app->request->isPjax) {
    //\yii\widgets\PjaxAsset::register($this);
}

//use yii\widgets\Pjax;
//Pjax::begin(['timeout' => false, 'id' => 'tester-p', 'enablePushState' => false, 'enableReplaceState' => false]);
foreach ($items as $data) { ?>
    <?php echo $this->render('_item', ['model' => $data]); ?>
<?php }

//Pjax::end();
?>