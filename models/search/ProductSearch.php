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
    // public $image;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['price_min', 'price_max', 'supplier_id', 'manufacturer_id'], 'integer'],
            // [['image'],'boolean'],
            [['name', 'slug', 'sku', 'price'], 'safe'],
            [['created_at', 'updated_at'], 'date', 'format' => 'php:Y-m-d']
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
            'sort' => self::getSort(),
            /*'sort22' => [
                //'defaultOrder' => ['created_at' => SORT_ASC],
                'attributes' => [
                    'price',
                    'created_at',
                    'name' => [
                        'asc' => ['translations.name' => SORT_ASC],
                        'desc' => ['translations.name' => SORT_DESC],
                    ]
                ],
            ],*/
        ]);
        if (isset($params[$className]['price']['min'])) {
            $this->price_min = $params[$className]['price']['min'];
            if (!is_numeric($this->price_min)) {
                $this->addError('price', Yii::t('yii', '{attribute} must be a number.', ['attribute' => 'min']));
                return $dataProvider;
            }
        }
        if (isset($params[$className]['price']['max'])) {
            $this->price_max = $params[$className]['price']['max'];
            if (!is_numeric($this->price_max)) {
                $this->addError('price', Yii::t('yii', '{attribute} must be a number.', ['attribute' => 'max']));
                return $dataProvider;
            }
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

        if (isset($params[$className]['price']['max'])) {
            $query->applyPrice($params[$className]['price']['max'], '<=');
        }
        if (isset($params[$className]['price']['min'])) {
            $query->applyPrice($params[$className]['price']['min'], '>=');
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
        $query->andFilterWhere(['like', 'supplier_id', $this->supplier_id]);
        $query->andFilterWhere(['like', 'manufacturer_id', $this->manufacturer_id]);


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
