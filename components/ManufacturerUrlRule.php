<?php

namespace panix\mod\shop\components;

use panix\mod\shop\models\Manufacturer;

/**
 * Class ManufacturerUrlRule
 * @package panix\mod\shop\components
 */
class ManufacturerUrlRule extends BaseUrlRule
{

    public function getAllPaths()
    {
        $allPaths = \Yii::$app->cache->get('ManufacturerUrlRule');
        if ($allPaths === false) {
            $allPaths = (new \yii\db\Query())
                ->select([$this->alias])
                ->from(Manufacturer::tableName())
                ->all();

            // Sort paths by length.
            usort($allPaths, function ($a, $b) {
                return strlen($b[$this->alias]) - strlen($a[$this->alias]);
            });

            \Yii::$app->cache->set('ManufacturerUrlRule', $allPaths, $this->cacheDuration);
        }

        return $allPaths;
    }

}
