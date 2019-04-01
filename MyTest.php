<?php
namespace panix\mod\shop;

class MyTest{

    public function init(){
        echo 'GGGGGGGGGGGGGGGGGGGGGGGGGGGG';
        rename(\Yii::getAlias('@shop').DIRECTORY_SEPARATOR."README.md", \Yii::getAlias('@shop').DIRECTORY_SEPARATOR."111README.md");
    }
}