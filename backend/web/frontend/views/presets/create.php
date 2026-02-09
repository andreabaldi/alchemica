<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \common\models\Presets $model */

$this->title = 'Create Presets';
$this->params['breadcrumbs'][] = ['label' => 'Presets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="presets-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
