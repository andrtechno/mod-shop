<?php

namespace panix\shop\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use panix\shop\models\ShopCurrency;

/**
 * ShopManufacturerSearch represents the model behind the search form about `panix\shop\models\ShopManufacturer`.
 */
class ShopCurrencySearch extends ShopCurrency {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id'], 'integer'],
            [['name','seo_alias','is_default'], 'safe'],
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
        $query = ShopCurrency::find();

        $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                    'sort'=> ['defaultOrder' => ['ordern'=>SORT_DESC]],
                    'pagination' => [
                        'pageSize' => Yii::$app->params['pagenum'],
                        ],
                ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);
        $query->andFilterWhere(['like', 'is_default', $this->is_default]);

        return $dataProvider;
    }

}
