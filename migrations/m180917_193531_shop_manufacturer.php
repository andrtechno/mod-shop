<?php

namespace panix\mod\shop\migrations;

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193531_shop_manufacturer
 */

use Yii;
use panix\engine\CMS;
use panix\engine\db\Migration;
use panix\mod\shop\models\Manufacturer;
use panix\mod\shop\models\translate\ManufacturerTranslate;

class m180917_193531_shop_manufacturer extends Migration
{

    public function up()
    {
        $this->createTable(Manufacturer::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'cat_id' => $this->integer()->null(),
            'image' => $this->string()->null(),
            'slug' => $this->string(255)->null(),
            'switch' => $this->boolean()->defaultValue(1),
            'name_ru' => $this->string(255)->null(),
            'name_uk' => $this->string(255)->null(),
            'description_ru' => $this->text()->null(),
            'description_uk' => $this->text()->null(),
            'ordern' => $this->integer()->unsigned(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);



        $this->createIndex('switch', Manufacturer::tableName(), 'switch');
        $this->createIndex('ordern', Manufacturer::tableName(), 'ordern');
        $this->createIndex('slug', Manufacturer::tableName(), 'slug');
        $this->createIndex('cat_id', Manufacturer::tableName(), 'cat_id');

        $brands = [
            [
                'name' => 'Apple',
                'image' => 'apple.png',
                'description' => 'Apple — американская корпорация, производитель персональных и планшетных компьютеров, аудиоплееров, телефонов, программного обеспечения. Один из пионеров в области персональных компьютеров и современных многозадачных операционных систем с графическим интерфейсом. Штаб-квартира — в Купертино, штат Калифорния.'
            ],
            [
                'name' => 'Asus',
                'image' => 'asus.png',
                'description' => 'AsusTek Computer Inc. — расположенная на Тайване транснациональная компания, специализирующаяся на компьютерной электронике (как комплектующие, так и готовые продукты). Название торговой марки Asus происходит от слова Pegasus («Пегас»). Котировки ценных бумаг: NASDAQ: AKCPF.'
            ],
            [
                'name' => 'Samsung',
                'image' => 'samsung.png',
                'description' => 'Samsung Group — южнокорейская группа компаний, один из крупнейших чеболей, основанный в 1938 году. На мировом рынке известен как производитель высокотехнологичных компонентов, включая полноцикловое производство интегральных микросхем, телекоммуникационного оборудования, бытовой техники, аудио- и видеоустройств.'
            ],
            [
                'name' => 'LG',
                'image' => 'lg.png',
                'description' => 'LG Electronics Inc. — южнокорейская компания, один из крупнейших мировых производителей потребительской электроники и бытовой техники. Входит в состав конгломерата LG Group. Главный офис компании LG Electronics находится в Сеуле, Республика Корея, 120 представительств компании открыты в 95 странах мира.'
            ],
            [
                'name' => 'Philips',
                'image' => 'philips.png',
                'description' => 'Koninklijke Philips N.V. — нидерландская транснациональная компания.'
            ],
            [
                'name' => 'Lenovo',
                'image' => 'lenovo.png',
                'description' => 'Lenovo Group Limited — китайская компания, выпускающая персональные компьютеры и другую электронику. Является крупнейшим производителем персональных компьютеров в мире с долей на рынке более 20 %, а также занимает пятое место по производству мобильных телефонов.'
            ],
            [
                'name' => 'Sony',
                'image' => 'sony.png',
                'description' => 'Sony Corporation, «Со́ни» — японская транснациональная корпорация со штаб-квартирой в Токио, образованная 7 мая 1946 года. Специализируется на выпуске домашней и профессиональной электроники, игровых консолей и другой высокотехнологичной продукции.'
            ],
            [
                'name' => 'YAMAHA',
                'image' => 'yamaha.png',
                'description' => 'Yamaha Corporation — японская транснациональная компания, крупнейший производитель музыкальных инструментов, также занимается производством акустических систем, звукового оборудования и спортивного инвентаря. Штаб-квартира компании расположена в г. Хамамацу (префектура Сидзуока). Основана 12 октября 1887 года.'
            ],
        ];
        $id = 1;
        foreach ($brands as $key => $brand) {
            $this->batchInsert(Manufacturer::tableName(), ['cat_id', 'slug', 'image', 'switch', 'ordern', 'name_ru', 'name_uk', 'description_ru', 'description_uk'], [
                [NULL, CMS::slug($brand['name']), $brand['image'], 1, $id, $brand['name'], $brand['name'], $brand['description'], $brand['description']]
            ]);
            $id++;
        }

        $this->loadColumns('grid-manufacturer', Manufacturer::class, ['image', 'name', 'products']);

    }

    public function down()
    {
        $this->dropTable(Manufacturer::tableName());
    }
}
