<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var \common\models\Presets $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="presets-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'technique_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'gamma_base')->textInput() ?>

    <?= $form->field($model, 'gamma_step')->textInput() ?>

    <?= $form->field($model, 'color_hex')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'ink_limit')->textInput() ?>

    <?= $form->field($model, 'paper_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'uv_exposure_seconds')->textInput() ?>

    <?= $form->field($model, 'notes')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
