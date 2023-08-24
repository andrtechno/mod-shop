<?php

namespace panix\mod\shop\controllers\admin;

use Yii;
use yii\rest\Controller;
use panix\mod\rbac\filters\AccessControl;
use panix\engine\taggable\Tag;

class JsonController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'allowActions' => [
                    // 'index',
                    // The actions listed here will be allowed to everyone including guests.
                ]
            ],
        ];
    }

    public function actionTagSuggest($q)
    {
        $tags = Tag::find()
            ->where(['LIKE', 'name', $q])
            ->asArray()
            ->all();
        $tagsList = [];
        foreach ($tags as $tag) {
            $tagsList[] = $tag['name'];
        }
        return $this->asJson($tagsList);
    }
}
