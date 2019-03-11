<?php

namespace panix\mod\shop\models\search;

use Yii;
use yii\base\Model;
use panix\engine\data\ActiveDataProvider;
use panix\mod\shop\models\Product;

/**
 * ProductSearch represents the model behind the search form about `panix\mod\shop\models\Product`.
 */
class ProductSearch extends Product
{

    public $exclude = null;
    public $price_min;
    public $price_max;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['price_min', 'price_max'], 'integer'],
            [['name', 'seo_alias', 'sku', 'price'], 'safe'],
            [['date_update', 'date_create'], 'date', 'format' => 'php:Y-m-d']
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
    public function search($params, $configure = array())
    {
        $query = Product::find();
        //$query->joinWith('translations');
        $query->sort();
        $query->joinWith(['translations translations']);

        $className = substr(strrchr(__CLASS__, "\\"), 1);


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            //'sort' => self::getSort()
            'sort' => [
                //'defaultOrder' => ['date_create' => SORT_ASC],
                'attributes' => [
                    'price',
                ],
            ],
        ]);


        if(isset($params[$className]['price']['min'])){
            $this->price_min = $params[$className]['price']['min'];
        }
        if(isset($params[$className]['price']['max'])){
            $this->price_max = $params[$className]['price']['max'];
        }


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
        ]);


        if (isset($params[$className]['eav'])) {
            $result = array();
            foreach ($params[$className]['eav'] as $name => $eav) {
                if (!empty($eav)) {
                    $result[$name][] = $eav;
                }
            }

            $query->getFindByEavAttributes2($result);
        }


        // Id of product to exclude from search
        if ($this->exclude) {
            foreach ($this->exclude as $id) {
                $query->andFilterWhere(['!=', self::tableName() . '.id', $id]);
            }
        }
        if (isset($configure['conf'])) {
            $query->andWhere(['IN', 'id', $configure['conf']]);
        }

        /*$query->andFilterWhere([
            '>=',
            'date_update',
            $this->date_update
        ]);*/


        // $query->andFilterWhere(['between', 'date_update', $this->start, $this->end]);
        //$query->andFilterWhere(['like', "DATE(CONVERT_TZ('date_update', 'UTC', '".Yii::$app->timezone."'))", $this->date_update.' 23:59:59']);
        //  $query->andFilterWhere(['like', "DATE(CONVERT_TZ('date_create', 'UTC', '".Yii::$app->timezone."'))", $this->date_create.]);

        $query->andFilterWhere(['like', 'translations.name', $this->name]);
        $query->andFilterWhere(['like', 'sku', $this->sku]);
        //$query->andFilterWhere(['like', 'price', $this->price]);

        //if ($this->price)
        //    $query->applyPrice($this->price);

        if ($this->price_max) {
            $query->applyMaxPrice($this->price_max);
        }
        if ($this->price_min) {
            $query->applyMinPrice($this->price_min);
        }

        return $dataProvider;
    }


    public function searchBySite($params)
    {
        $query = Product::find();
        $query->joinWith('translations');
        $this->load($params);
        return $query;
    }

}
