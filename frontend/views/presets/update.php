<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \common\models\Presets $model */

$this->title = 'Update Presets: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Presets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="presets-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
