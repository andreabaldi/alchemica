<?php

use common\models\Presets;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var frontend\models\PresetsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Presets Tecnici';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="presets-index">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= Html::encode($this->title) ?></h1>
        <?= Html::a('<i class="fas fa-plus"></i> Create Preset', ['create'], ['class' => 'btn btn-success shadow-sm']) ?>
    </div>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'tableOptions' => ['class' => 'table table-hover shadow-sm bg-white rounded'],
            'columns' => [
                    [
                            'attribute' => 'technique_name',
                            'format' => 'raw',
                            'value' => function($model) {
                                return Html::tag('strong', $model->technique_name) .
                                        Html::tag('div', $model->paper_name, ['class' => 'small text-muted']);
                            }
                    ],
                // COLONNA DINAMICA GAMMA
                    [
                            'label' => 'Configurazione Gamma',
                            'format' => 'raw',
                            'value' => function($model) {
                                if ($model->gamma_mode === 'list') {
                                    // Se è una lista, creiamo dei piccoli badge
                                    $items = is_array($model->gamma_custom_list) ? $model->gamma_custom_list : explode(',', $model->gamma_custom_list);
                                    $html = '<div class="gamma-list-container">';
                                    foreach ($items as $val) {
                                        if (!empty($val)) {
                                            $html .= Html::tag('span', trim($val), [
                                                    'class' => 'badge badge-info mr-1',
                                                    'style' => 'font-size: 0.9em; padding: 5px 8px;'
                                            ]);
                                        }
                                    }
                                    return $html . '</div>' . Html::tag('small', 'Modalità: Lista', ['class' => 'text-success d-block mt-1 font-italic']);
                                } else {
                                    // Altrimenti mostriamo la logica Step classica
                                    return "Base: <strong>{$model->gamma_base}</strong><br>" .
                                            "Step: <strong>+{$model->gamma_step}</strong>" .
                                            Html::tag('small', 'Modalità: Step', ['class' => 'text-primary d-block mt-1 font-italic']);
                                }
                            }
                    ],
                    [
                            'attribute' => 'paper_name',
                            'value' => function($model) {
                                return $model->paper_name;
                            }
                    ],
                    [
                            'attribute' => 'uv_exposure_seconds',
                            'label' => 'Esposizione',
                            'value' => function($model) {
                                return $model->uv_exposure_seconds ? $model->uv_exposure_seconds . 's' : '-';
                            }
                    ],
                    [
                            'attribute' => 'notes',
                            'value' => function($model) {
                                return $model->notes;
                            }
                    ],
                    [
                            'class' => ActionColumn::className(),
                            'template' => '{update} {delete}', // Spesso index è più pulita così
                            'urlCreator' => function ($action, Presets $model, $key, $index, $column) {
                                return Url::toRoute([$action, 'id' => $model->id]);
                            }
                    ],
            ],
    ]); ?>

</div>

<style>
    .badge-info { background-color: #17a2b8; color: white; }
    .table thead th { border-top: none; background: #f8f9fa; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
</style>