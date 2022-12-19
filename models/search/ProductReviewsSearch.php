<?php

namespace panix\mod\shop\models\search;

use Yii;
use yii\base\Model;
use panix\engine\data\ActiveDataProvider;
use panix\mod\shop\models\ProductReviews;

/**
 * ProductReviewsSearch represents the model behind the search form about `panix\shop\models\Brand`.
 */
class ProductReviewsSearch extends ProductReviews
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'product_id', 'rate'], 'integer'],
            [['text', 'user_name'], 'string'],
            [['text'], 'safe'],
        ];
    }

    public function init()
    {
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = ProductReviews::find()
            ->where(['depth' => 1])
            ->orderBy(['status' => SORT_ASC, 'created_at' => SORT_DESC]);//->groupBy('product_id');

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
        $query->andFilterWhere(['product_id' => $this->product_id]);
        $query->andFilterWhere(['like', 'text', $this->text]);
        $query->andFilterWhere(['like', 'rate', $this->rate]);
        $query->andFilterWhere(['like', 'user_name', $this->user_name]);

        return $dataProvider;
    }

}
