<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\PresetsSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="presets-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'technique_name') ?>

    <?= $form->field($model, 'gamma_base') ?>

    <?= $form->field($model, 'gamma_step') ?>

    <?= $form->field($model, 'color_hex') ?>

    <?php // echo $form->field($model, 'ink_limit') ?>

    <?php // echo $form->field($model, 'paper_name') ?>

    <?php // echo $form->field($model, 'uv_exposure_seconds') ?>

    <?php // echo $form->field($model, 'notes') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
