<?php

/**
 * Generation migrate by PIXELION CMS
 * @author PIXELION CMS development team <dev@pixelion.com.ua>
 *
 * Class m180917_193517_shop_currency
 */

use panix\engine\db\Migration;
use panix\mod\shop\models\Currency;

class m180917_193517_shop_currency extends Migration
{

    public function up()
    {
        $this->createTable(Currency::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(255)->null(),
            'iso' => $this->string(10)->null()->defaultValue(null),
            'symbol' => $this->string(10)->notNull()->defaultValue(null),
            'rate' => $this->money(10, 2)->notNull()->defaultValue(null),
            'penny' => $this->string(5)->null()->defaultValue(null),
            'separator_hundredth' => $this->string(5)->null()->defaultValue(null),
            'separator_thousandth' => $this->string(5)->null()->defaultValue(null),
            'is_main' => $this->boolean()->defaultValue(false),
            'is_default' => $this->boolean()->defaultValue(false),
            'switch' => $this->boolean()->defaultValue(true),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'ordern' => $this->integer()->unsigned(),
        ]);
        $this->createIndex('is_main', Currency::tableName(), 'is_main');
        $this->createIndex('is_default', Currency::tableName(), 'is_default');
        $this->createIndex('ordern', Currency::tableName(), 'ordern');
        $this->createIndex('updated_at', Currency::tableName(), 'updated_at');

        $this->createTable('{{%shop__currency_history}}', [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned(),
            'currency_id' => $this->integer()->null(),
            'rate' => $this->money(10, 2)->defaultValue(null),
            'created_at' => $this->integer(),
        ]);


        $this->createIndex('currency_id', '{{%shop__currency_history}}', 'currency_id');
        $this->createIndex('user_id', '{{%shop__currency_history}}', 'user_id');
        $this->createIndex('created_at', '{{%shop__currency_history}}', 'created_at');

        $columns = ['name', 'iso', 'symbol', 'rate', 'penny', 'separator_hundredth', 'separator_thousandth', 'is_main', 'is_default', 'switch', 'created_at', 'ordern'];
        $this->batchInsert(Currency::tableName(), $columns, [
            ['Гривна', 'UAH', 'грн.', 1, 0, ' ', ' ', 1, 1, 1, time(), 85],
            ['Russian Ruble', 'RUB', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 84],
            ['United States Dollar', 'USD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 83],
            ['Euro', 'EUR', '€', 1, 0, ' ', ' ', 0, 0, 0, time(), 82],
            ['United Arab Emirates Dirham', 'AED', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 81],
            ['Afghan Afghani', 'AFN', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 80],
            ['Albanian Lek', 'ALL', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 79],
            ['Armenian Dram', 'AMD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 75],
            ['Argentine Peso', 'ARS', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 74],
            ['Australian Dollar', 'AUD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 76],
            ['Azerbaijani Manat', 'AZN', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 75],
            ['Bosnia & Herzegovina Convertible Mark', 'BAM', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 74],
            ['Bangladeshi Taka', 'BDT', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 73],
            ['Bulgarian Lev', 'BGN', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 72],
            ['Brunei Dollar', 'BND', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 71],
            ['Bolivian Boliviano', 'BOB', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 70],
            ['Brazilian Real', 'BRL', 'R$', 1, 0, ' ', ' ', 0, 0, 0, time(), 69],
            ['Bulgarian', 'BGN', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 68],
            ['Canadian Dollar', 'CAD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 67],
            ['Swiss Franc', 'CHF', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 66],
            ['Chilean Peso', 'CLP', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 65],
            ['Chinese Renminbi Yuan', 'CNY', 'CN¥', 1, 0, ' ', ' ', 0, 0, 0, time(), 64],
            ['Colombian Peso', 'COP', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 63],
            ['Costa Rican Colón', 'CRC', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 62],
            ['Czech Koruna', 'CZK', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 61],
            ['Danish Krone', 'DKK', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 60],
            ['Dominican Peso', 'DOP', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 59],
            ['Algerian Dinar', 'DZD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 58],
            ['Egyptian Pound', 'EGP', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 57],
            ['Japanese Yen', 'JPY', '¥', 1, 0, ' ', ' ', 0, 0, 0, time(), 56],
            ['British Pound', 'GBP', '£', 1, 0, ' ', ' ', 0, 0, 0, time(), 55],
            ['Georgian Lari', 'GEL', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 54],
            ['Guatemalan Quetzal', 'GTQ', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 53],
            ['Hong Kong Dollar', 'HKD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 52],
            ['Honduran Lempira', 'HNL', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 51],
            ['Croatian Kuna', 'HRK', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 50],
            ['Hungarian Forint', 'HUF', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 49],
            ['Indonesian Rupiah', 'IDR', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 48],
            ['Israeli New Sheqel', 'ILS', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 47],
            ['Indian Rupee', 'INR', '₹', 1, 0, ' ', ' ', 0, 0, 0, time(), 46],
            ['Icelandic Króna', 'ISK', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 45],
            ['Jamaican Dollar', 'JMD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 44],
            ['Kenyan Shilling', 'KES', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 43],
            ['Kyrgyzstani Som', 'KGS', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 42],
            ['South Korean Won', 'KRW', '₩', 1, 0, ' ', ' ', 0, 0, 0, time(), 41],
            ['Kazakhstani Tenge', 'KZT', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 40],
            ['Lebanese Pound', 'LBP', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 39],
            ['Sri Lankan Rupee', 'LKR', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 38],
            ['Moroccan Dirham', 'MAD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 37],
            ['Moldovan Leu', 'MDL', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 36],
            ['Mongolian Tögrög', 'MNT', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 35],
            ['Mauritian Rupee', 'MUR', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 34],
            ['Maldivian Rufiyaa', 'MVR', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 33],
            ['Mexican Peso', 'MXN', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 32],
            ['Malaysian Ringgit', 'MYR', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 31],
            ['Mozambican Metical', 'MZN', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 30],
            ['Nigerian Naira', 'NGN', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 29],
            ['Nicaraguan Córdoba', 'NIO', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 28],
            ['Norwegian Krone', 'NOK', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 27],
            ['Nepalese Rupee', 'NPR', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 26],
            ['New Zealand Dollar', 'NZD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 25],
            ['Panamanian Balboa', 'PAB', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 24],
            ['Peruvian Nuevo Sol', 'PEN', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 23],
            ['Philippine Peso', 'PHP', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 22],
            ['Pakistani Rupee', 'PKR', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 21],
            ['Polish Złoty', 'PLN', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 20],
            ['Paraguayan Guaraní', 'PYG', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 19],
            ['Qatari Riyal', 'QAR', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 18],
            ['Romanian Leu', 'RON', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 17],
            ['Serbian Dinar', 'RSD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 16],
            ['Saudi Riyal', 'SAR', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 15],
            ['Swedish Krona', 'SEK', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 14],
            ['Singapore Dollar', 'SGD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 13],
            ['Thai Baht', 'THB', '฿', 1, 0, ' ', ' ', 0, 0, 0, time(), 12],
            ['Tajikistani Somoni', 'TJS', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 11],
            ['Turkish Lira', 'TRY', 'TRY', 1, 0, ' ', ' ', 0, 0, 0, time(), 10],
            ['Trinidad and Tobago Dollar', 'TTD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 9],
            ['New Taiwan Dollar', 'TWD', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 8],
            ['Tanzanian Shilling', 'TZS', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 7],
            ['Ugandan Shilling', 'UGX', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 6],
            ['Uruguayan Peso', 'UYU', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 5],
            ['Uzbekistani Som', 'UZS', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 4],
            ['Vietnamese Đồng', 'VND', '₫', 1, 0, ' ', ' ', 0, 0, 0, time(), 3],
            ['Yemeni Rial', 'YER', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 2],
            ['South African Rand', 'ZAR', '', 1, 0, ' ', ' ', 0, 0, 0, time(), 1],

        ]);
    }

    public function down()
    {
        $this->dropTable(Currency::tableName());

    }

}
