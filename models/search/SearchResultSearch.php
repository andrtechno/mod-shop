<?php

namespace panix\mod\shop\models\search;

use Yii;
use yii\base\Model;
use panix\engine\data\ActiveDataProvider;
use panix\mod\shop\models\SearchResult;

/**
 * SearchResultSearch represents the model behind the search form about `panix\shop\models\SearchResult`.
 */
class SearchResultSearch extends SearchResult
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['query'], 'safe'],
            [['created_at'], 'date', 'format' => 'php:Y-m-d']
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SearchResult::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],

        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['like', 'query', $this->query]);

        $timezone = Yii::$app->settings->get('app', 'timezone');
        $date_utc2 = new \DateTime();
        $date_utc2->setTimezone(new \DateTimeZone($timezone));

        if ($this->created_at) {
            list($year, $month, $day) = explode('-', $this->created_at);
            $date_utc2->setDate($year, $month, $day)->setTime(0, 0, 0, 0);

            $from_date = $date_utc2->getTimestamp();
            $to_date = $date_utc2->modify('+1 day')->getTimestamp() - 1;
            $query->andFilterWhere(['between', 'created_at', $from_date, $to_date]);
        }
        return $dataProvider;
    }

}
