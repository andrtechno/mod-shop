<?php

namespace panix\mod\shop\commands;


use Yii;
use panix\engine\console\controllers\ConsoleController;
use panix\mod\shop\models\Attribute;
use panix\mod\shop\models\Category;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\AttributeGroup;
use panix\mod\shop\models\AttributeOption;
use panix\mod\shop\models\Currency;
use panix\mod\shop\models\Product;
use panix\mod\shop\models\ProductAttributesEav;
use panix\mod\shop\models\ProductCategoryRef;
use panix\mod\shop\models\ProductNotifications;
use panix\mod\shop\models\ProductPrices;
use panix\mod\shop\models\ProductType;
use panix\mod\shop\models\ProductVariant;
use panix\mod\shop\models\RelatedProduct;
use panix\mod\shop\models\Sets;
use panix\mod\shop\models\SetsProduct;
use panix\mod\shop\models\Supplier;
use panix\mod\shop\models\TypeAttribute;

/**
 * @package panix\mod\shop\commands
 */
class DevController extends ConsoleController
{

    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }

    public function actionIndex(){
        $db = Yii::$app->db;
        $db->createCommand()->dropTable(Attribute::tableName());
       /* $db->createCommand()->dropTable(AttributeGroup::tableName());
        $db->createCommand()->dropTable(AttributeOption::tableName());
        $db->createCommand()->dropTable(Category::tableName());
        $db->createCommand()->dropTable(Currency::tableName());
        $db->createCommand()->dropTable(Manufacturer::tableName());
        $db->createCommand()->dropTable(ProductAttributesEav::tableName());
        $db->createCommand()->dropTable(ProductCategoryRef::tableName());
        $db->createCommand()->dropTable(ProductNotifications::tableName());
        $db->createCommand()->dropTable(ProductPrices::tableName());
        $db->createCommand()->dropTable(Product::tableName());


        $db->createCommand()->dropTable(TypeAttribute::tableName());
        $db->createCommand()->dropTable(ProductType::tableName());
        $db->createCommand()->dropTable(ProductVariant::tableName());
        $db->createCommand()->dropTable(RelatedProduct::tableName());
        $db->createCommand()->dropTable(Sets::tableName());
        $db->createCommand()->dropTable(SetsProduct::tableName());
        $db->createCommand()->dropTable(Supplier::tableName());*/


        echo 's';
    }
}
