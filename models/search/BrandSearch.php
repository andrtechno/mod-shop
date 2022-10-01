<?php

namespace panix\mod\shop\models\search;

use Yii;
use yii\base\Model;
use panix\engine\data\ActiveDataProvider;
use panix\mod\shop\models\Brand;

/**
 * BrandSearch represents the model behind the search form about `panix\shop\models\Brand`.
 */
class BrandSearch extends Brand {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id'], 'integer'],
            [['name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params) {
        $query = Brand::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
           // 'sort' => self::getSort()
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name_' . Yii::$app->language, $this->name]);

        return $dataProvider;
    }

}
