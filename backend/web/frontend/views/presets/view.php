<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var \common\models\Presets $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Presets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="presets-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'technique_name',
            'gamma_base',
            'gamma_step',
            'color_hex',
            'ink_limit',
            'paper_name',
            'uv_exposure_seconds',
            'notes:ntext',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
