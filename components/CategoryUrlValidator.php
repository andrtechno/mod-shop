<?php

namespace panix\mod\shop\components;

use Yii;
use yii\helpers\Html;
use panix\engine\validators\UrlValidator;
use panix\engine\assets\ValidationAsset;

class CategoryUrlValidator extends UrlValidator
{

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        /** @var \yii\db\ActiveRecord $model */
        $parent_slug = false;
        if (!$model->isNewRecord) {

            $check = $model::find()
                ->where([$this->attributeSlug => $model->$attribute])
                ->one();

        } else {


            $data = $model::findOne($model->primaryKey);
			if($data){
				$parent = $data->parent()->one();
				if ($parent) {
					$parent_slug = $parent->full_path;
				}
			}

        }


        if ($parent_slug) {
            $this->addError($model, $attribute, $this->message);
        }
        $model->{$this->attributeSlug} = mb_strtolower($model->{$this->attributeSlug});
        return null;
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        /** @var \yii\db\ActiveRecord|\panix\engine\behaviors\nestedsets\NestedSetsBehavior $model */
        $inputId = Html::getInputId($model, $attribute);
        ValidationAsset::register($view);
        $options = [
            'model' => get_class($model),
            'pk' => $model->primaryKey,
            'usexhr' => false,
            'successMessage' => $this->message,
            'AttributeSlug' => $attribute,
            'AttributeSlugId' => $inputId,
            'attributeCompareId' => Html::getInputId($model, $this->attributeCompare),
        ];
        if (Yii::$app->language == Yii::$app->languageManager->default->code && $model->isNewRecord) {
            $view->registerJs("init_translitter(" . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ");");
        }
        /** @var \yii\db\ActiveRecord $model */


        $parent_slug = false;
        if ($model->isNewRecord) {

        } else {
            /** @var \yii\db\ActiveRecord|\panix\engine\behaviors\nestedsets\NestedSetsBehavior $data */
            $data = $model::findOne($model->id);
            $parent = $data->parent()->one();
            if ($parent) {

                $parent_slug = $parent->full_path;

            }
        }

        $b = $this->getSlugs($model, $parent_slug);

        $jsonList = json_encode($b);

        return <<<JS
        var list = {$jsonList};
        var value = $("#{$inputId}").val();
        var parent = "{$parent_slug}";

if(list.indexOf(parent+'/'+value) !== -1){
    messages.push("{$this->message}");
}else{
    messages = null;
}
JS;
    }

    protected function getSlugs($model, $currentItem = false)
    {
        /** @var \yii\db\ActiveRecord $model */
        $table = $model::tableName();
        if ($currentItem) {
            $list = $model::getDb()->createCommand("SELECT full_path FROM {$table} WHERE id != 1 AND slug='{$currentItem}'")->queryAll();
        } else {
            $list = $model::getDb()->createCommand("SELECT full_path FROM {$table} WHERE id != 1")->queryAll();
        }

        $b = [];
        foreach ($list as $l) {
            $b[] = $l['full_path'];
        }
        return $b;
    }
}
