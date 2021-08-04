<?php

namespace panix\mod\shop\components;

/**
 * Find objects by external id
 */


use Yii;
use yii\db\Query;
use panix\mod\images\models\Image;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Brand;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\Supplier;

class ExternalFinder2
{


    const OBJECT_CATEGORY = 1;
    const OBJECT_ATTRIBUTE = 2;
    const OBJECT_PRODUCT = 3;
    const OBJECT_BRAND = 4;
    const OBJECT_ATTRIBUTE_OPTION = 5;
    const OBJECT_IMAGE = 6;
    const OBJECT_SUPPLIER = 7;
    const OBJECT_MAIN_CATEGORY = 8;
    public $cacheData;
    public $table = '{{%exchange}}';

    /**
     * @param $type
     * @param $externalId
     * @param bool $loadModel
     * @param bool $object_id
     * @return array|mixed $query
     */
    public function getObject($type, $externalId, $loadModel = true, $object_id = false)
    {

        if (isset($this->cacheData[$type][$externalId]))
            return $this->cacheData[$type][$externalId];

        if ($object_id) {
            $query = Yii::$app->db->createCommand()
                //->select("*")
                ->from($this->table)
                ->where('object_type=:type AND external_id=:externalId AND object_id=:object_id', [
                    ':type' => $type,
                    ':externalId' => $externalId,
                    ':object_id' => $object_id
                ])
                ->limit(1)
                ->queryOne();
        } else {

            $query = (new Query())
                ->select('*')
                ->from($this->table)
                ->where('object_type=:type AND external_id=:externalId', [
                    ':type' => $type,
                    ':externalId' => $externalId
                ])
                ->limit(1)
                ->createCommand()
                ->queryOne();

        }

        if ($query === false)
            return false;

        if ($loadModel === true && $query['object_id']) {
            switch ($type) {
                case self::OBJECT_CATEGORY:
                    $data = Category::findOne($query['object_id']);
                    $this->cacheData[$type][$externalId] = $data;
                    return $data;
                    break;

                case self::OBJECT_MAIN_CATEGORY:
                    $data = Category::findOne($query['object_id']);
                    $this->cacheData[$type][$externalId] = $data;
                    return $data;
                    break;

                case self::OBJECT_ATTRIBUTE:
                    $data = Attribute::findOne($query['object_id']);
                    $this->cacheData[$type][$externalId] = $data;
                    return $data;
                    break;

                case self::OBJECT_PRODUCT:
                    $data = Product::findOne($query['object_id']);
                    $this->cacheData[$type][$externalId] = $data;
                    return $data;
                    break;

                case self::OBJECT_BRAND:
                    $data = Brand::findOne($query['object_id']);
                    return $data;
                    break;

                case self::OBJECT_ATTRIBUTE_OPTION:
                    $data = AttributeOption::findOne($query['object_id']);
                    return $data;
                    break;

                case self::OBJECT_SUPPLIER:
                    $data = Supplier::findOne($query['object_id']);
                    return $data;
                    break;

                case self::OBJECT_IMAGE:
                    $data = Image::findOne($query['object_id']);
                    $this->cacheData[$type][$externalId] = $data;
                    return $data;
                    break;
            }
        }
        $this->cacheData[$type][$externalId] = $query['object_id'];
        return $query['object_id'];
    }

    public function removeObject($type, $external_id, $loadModel = true)
    {

        Yii::$app->db->createCommand()->delete(
            $this->table,
            'object_type=:type AND external_id=:external_id',
            array(
                ':type' => $type,
                ':external_id' => $external_id

            )
        );

        $img = self::getObject($type, $external_id, $loadModel);
        if ($img)
            $img->delete();
    }

    public function removeObjectByPk($type, $obj_id)
    {
        $query = Yii::$app->db->createCommand()->delete(
            $this->table,
            'object_type=:type AND object_id=:object_id',
            array(
                ':type' => $type,
                ':object_id' => $obj_id
            )
        );

    }

    public function removeObjectByPk__Old($type, $obj_id)
    {
        $query = Yii::$app->db->createCommand()
            ->from($this->table)
            ->where('object_type=:type AND object_id=:obj_id', array(
                ':type' => $type,
                ':obj_id' => $obj_id
            ))
            ->delete();
    }

    /**
     * Create external
     *
     * @param $type
     * @param $id
     * @param $externalId
     * @return int
     */
    public function createExternalId($type, $id, $externalId)
    {
        Yii::$app->db->createCommand()->insert($this->table, [
            'object_type' => $type,
            'object_id' => $id,
            'external_id' => $externalId
        ])->execute();
    }
}
