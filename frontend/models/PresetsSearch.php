<?php

namespace frontend\models;

use common\models\Presets;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * PresetsSearch represents the model behind the search form of `frontend\models\Presets`.
 */
class PresetsSearch extends Presets
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'ink_limit', 'uv_exposure_seconds', 'created_at', 'updated_at'], 'integer'],
            [['technique_name', 'color_hex', 'paper_name', 'notes'], 'safe'],
            [['gamma_base', 'gamma_step'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
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
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Presets::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'gamma_base' => $this->gamma_base,
            'gamma_step' => $this->gamma_step,
            'ink_limit' => $this->ink_limit,
            'uv_exposure_seconds' => $this->uv_exposure_seconds,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'technique_name', $this->technique_name])
            ->andFilterWhere(['like', 'color_hex', $this->color_hex])
            ->andFilterWhere(['like', 'paper_name', $this->paper_name])
            ->andFilterWhere(['like', 'notes', $this->notes]);

        return $dataProvider;
    }
}
