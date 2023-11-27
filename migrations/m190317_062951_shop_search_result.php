<?php

/**
 * Generation migrate by PIXELION CMS
 *
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 * @link http://pixelion.com.ua PIXELION CMS
 *
 * Class m190317_062951_shop_search_result
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\SearchResult;

class m190317_062951_shop_search_result extends Migration
{

    public function up()
    {
        $this->createTable(SearchResult::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned()->defaultValue(null),
            'query' => $this->string()->null()->defaultValue(null),
            'result' => $this->integer()->null()->defaultValue(0),
            'ip_create' => $this->string(50)->null()->defaultValue(null),
            'user_agent' => $this->text()->null()->defaultValue(null),
            'created_at' => $this->integer(),
        ]);

    }

    public function down()
    {
        $this->dropTable(SearchResult::tableName());
    }

}
