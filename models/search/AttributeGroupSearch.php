<?php

namespace panix\mod\shop\models\search;

use yii\base\Model;
use panix\engine\data\ActiveDataProvider;
use panix\mod\shop\models\AttributeGroup;

/**
 * AttributeGroupSearch represents the model behind the search form about `panix\shop\models\search\AttributeGroupSearch`.
 */
class AttributeGroupSearch extends AttributeGroup
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @inheritdoc
     */
    public function search($params)
    {
        $query = AttributeGroup::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['ordern' => SORT_DESC]],

        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);


        return $dataProvider;
    }

}
