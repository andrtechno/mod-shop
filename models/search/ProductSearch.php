<?php

namespace panix\mod\shop\models\search;

use Yii;
use yii\base\Model;
use panix\engine\data\ActiveDataProvider;
use panix\mod\shop\models\Product;

/**
 * PagesSearch represents the model behind the search form about `app\modules\pages\models\Pages`.
 */
class ProductSearch extends Product
{

    public $exclude = null;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'price'], 'integer'],
            [['name', 'seo_alias', 'sku', 'price'], 'safe'],
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

        $query->andFilterWhere(['like', 'translations.name', $this->name]);
        $query->andFilterWhere(['like', 'sku', $this->sku]);
        $query->andFilterWhere(['like', 'price', $this->price]);

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
