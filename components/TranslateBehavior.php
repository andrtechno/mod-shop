<?php

namespace panix\mod\shop\components;

use panix\engine\CMS;
use panix\mod\admin\components\YandexTranslate;
use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * TranslateBehavior
 *
 * @property ActiveRecord $owner
 */
class TranslateBehavior extends Behavior
{

    /**
     * @var string[] the list of attributes to be translated
     */
    public $translationAttributes;
    private $manager;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            // ActiveRecord::EVENT_AFTER_VALIDATE => 'afterValidate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->manager = Yii::$app->languageManager;
        if ($this->translationAttributes === null) {
            throw new InvalidConfigException('The "translationAttributes" property must be set.');
        }
    }


    /**
     * @return void
     */
    public function afterValidate()
    {
        if (!Model::validateMultiple($this->owner->{$this->relation})) {
            $this->owner->addError($this->relation);
        }
    }


    /**
     * @return void
     */
    public function afterSave()
    {
        $owner = $this->owner;
        if ($owner->isNewRecord) {
            foreach (Yii::$app->languageManager->languages as $language) {
                foreach ($this->translationAttributes as $attr) {
                    $owner->{$attr . '_' . $language->code} = $owner->{$attr};
                    //  $owner->setAttribute($attr . '_' . $language->code, $owner->{$attr});
                }

            }
            $owner->save(false);
        }

    }


    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return in_array($name, $this->translationAttributes) ?: parent::canGetProperty($name, $checkVars);
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return in_array($name, $this->translationAttributes) ?: parent::canSetProperty($name, $checkVars);
    }


    /**
     * @inheritdoc
     */
    public function __get($name)
    {

        $owner = $this->owner;
        $attribute = $owner->getAttribute($name . '_' . Yii::$app->language);

        if (!$attribute) {
            $attribute = $owner->getAttribute($name . '_' . $this->manager->default->code);
        }
        return $attribute;
    }


    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        $this->owner->setAttribute($name . '_' . Yii::$app->language, $value);
    }

}
