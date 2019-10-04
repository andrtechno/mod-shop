<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 *
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * @link http://pixelion.com.ua PIXELION CMS
 *
 * Class m190316_061840_insert_shop_attribute
 */

use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\translate\AttributeOptionTranslate;
use Yii;
use panix\engine\CMS;
use panix\engine\db\Migration;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\translate\AttributeTranslate;

class m190316_061840_insert_shop_attribute extends Migration
{

    public function up()
    {

        $list = [
            'Размер' => [
                'type' => Attribute::TYPE_DROPDOWN,
                'display_on_front' => true,
                'use_in_filter' => true,
                'use_in_variants' => true,
                'use_in_compare' => true,
                'select_many' => true,
                'required' => true,
                'options' => ['S', 'M', 'L']
            ],
        ];
        $i = 1;
        foreach ($list as $name => $data) {
            $this->batchInsert(Attribute::tableName(), ['name', 'type', 'display_on_front', 'use_in_filter', 'use_in_variants', 'use_in_compare', 'select_many', 'required'], [
                [CMS::slug($name), $data['type'], $data['display_on_front'], $data['use_in_filter'], $data['use_in_variants'], $data['use_in_compare'], $data['select_many'], $data['required']]
            ]);

            foreach (Yii::$app->languageManager->getLanguages(false) as $lang) {
                $this->batchInsert(AttributeTranslate::tableName(), ['object_id', 'language_id', 'title', 'abbreviation', 'hint'], [
                    [$i, $lang['id'], $name, '', '']
                ]);
            }


            if (isset($data['options'])) {
                $o = 1;
                foreach ($data['options'] as $option) {

                    $this->batchInsert(AttributeOption::tableName(), ['attribute_id', 'ordern'], [
                        [$o, $o]
                    ]);

                    foreach (Yii::$app->languageManager->getLanguages(false) as $lang) {
                        $this->batchInsert(AttributeOptionTranslate::tableName(), ['object_id', 'language_id', 'value'], [
                            [$o, $lang['id'], $option]
                        ]);
                        echo $option;
                    }
                    $o++;
                }
            }

            $i++;
        }

    }

    public function down()
    {

    }

}
