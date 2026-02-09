<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;

/** @var yii\web\View $this */
/** @var common\models\Presets $model */
/** @var yii\widgets\ActiveForm $form */
?>

    <div class="presets-form">
        <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-dark text-white p-4">
                <h4 class="mb-0"><i class="fas fa-sliders-h mr-2"></i> Configurazione Preset Tecnica</h4>
                <small class="text-muted">Imposta i parametri matematici per la calibrazione e l'esposizione UV.</small>
            </div>

            <div class="card-body p-4 bg-light">
                <?php $form = ActiveForm::begin([
                        'options' => ['class' => 'modern-form'],
                        'fieldConfig' => [
                                'template' => "{label}\n{input}\n{error}",
                                'labelOptions' => ['class' => 'font-weight-bold text-uppercase small text-muted'],
                                'inputOptions' => ['class' => 'form-control form-control-lg border-0 shadow-sm'],
                        ],
                ]); ?>

                <div class="row">
                    <div class="col-md-6">
                        <?= $form->field($model, 'technique_name')->textInput(['placeholder' => 'Es: Platino/Palladio, Cianotipia...']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'paper_name')->textInput(['placeholder' => 'Es: Arches Platine']) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($model, 'color_hex')->input('color', ['style' => 'height: 50px; padding: 5px;']) ?>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="bg-white p-3 rounded shadow-sm d-flex align-items-center justify-content-between">
                            <div>
                                <label class="font-weight-bold text-uppercase small text-muted mb-0 mr-3">Logica Generazione:</label>
                                <?= $form->field($model, 'gamma_mode')->radioList([
                                        'step' => 'Progressione (Base + Step)',
                                        'list' => 'Lista Valori Predefiniti'
                                ], [
                                        'item' => function($index, $label, $name, $checked, $value) {
                                            $check = $checked ? 'checked' : '';
                                            return "
                                        <div class='custom-control custom-radio custom-control-inline'>
                                            <input type='radio' id='mode-$value' name='$name' value='$value' class='custom-control-input' $check>
                                            <label class='custom-control-label' for='mode-$value'>$label</label>
                                        </div>";
                                        }
                                ])->label(false) ?>
                            </div>
                            <div class="text-right">
                                <?= $form->field($model, 'show_wedge_default')->checkbox(['class' => 'custom-control-input'])->label(null, ['class' => 'custom-control-label']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mode-step-fields">
                        <div class="p-3 bg-white rounded shadow-sm border-left border-primary" style="border-left-width: 5px !important;">
                            <?= $form->field($model, 'gamma_base')->textInput(['type' => 'number', 'step' => '0.01']) ?>
                            <small class="text-info font-italic">Partenza griglia.</small>
                        </div>
                    </div>
                    <div class="col-md-4 mode-step-fields">
                        <div class="p-3 bg-white rounded shadow-sm border-left border-info" style="border-left-width: 5px !important;">
                            <?= $form->field($model, 'gamma_step')->textInput(['type' => 'number', 'step' => '0.01']) ?>
                            <small class="text-info font-italic">Incremento cella.</small>
                        </div>
                    </div>

                    <div class="col-md-8 mode-list-fields" style="display:none;">
                        <div class="p-3 bg-white rounded shadow-sm border-left border-success" style="border-left-width: 5px !important;">
                            <?= $form->field($model, 'gamma_custom_list')->widget(Select2::class, [
                                    'data' => [
                                            '1.0' => '1.0 (Lineare)',
                                            '1.4' => '1.4 (Sperimentale)',
                                            '1.8' => '1.8 (Standard Apple)',
                                            '2.2' => '2.2 (Standard PC/sRGB)',
                                            '2.4' => '2.4 (Rec.709)',
                                            '3.0' => '3.0 (Contrasto Alto)'
                                    ],
                                    'options' => [
                                            'placeholder' => 'Seleziona o digita valori (es: 1.6)...',
                                            'multiple' => true,
                                    ],
                                    'pluginOptions' => [
                                            'tags' => true, // Permette l'inserimento manuale
                                            'tokenSeparators' => [',', ' '],
                                            'maximumInputLength' => 4
                                    ],
                            ])->label('Lista Gamma Personalizzati') ?>
                            <small class="text-success font-italic">Puoi inserire valori liberi tra 0.5 e 4.5.</small>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded shadow-sm border-left border-warning" style="border-left-width: 5px !important;">
                            <?= $form->field($model, 'ink_limit')->textInput(['type' => 'number', 'max' => 100]) ?>
                            <small class="text-info font-italic">Inchiostro Max (%).</small>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <?= $form->field($model, 'uv_exposure_seconds')->textInput(['type' => 'number', 'placeholder' => 'Secondi']) ?>
                    </div>
                    <div class="col-md-8">
                        <?= $form->field($model, 'notes')->textarea(['rows' => 3]) ?>
                    </div>
                </div>

                <div class="form-group mt-5 text-right">
                    <?= Html::a('Annulla', ['index'], ['class' => 'btn btn-link text-muted mr-3']) ?>
                    <?= Html::submitButton('<i class="fas fa-save mr-1"></i> Salva Preset', ['class' => 'btn btn-primary btn-lg px-5 shadow']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>

<?php
// JAVASCRIPT PER IL TOGGLE DINAMICO
$js = <<<JS
    function updateGammaVisibility() {
        var mode = $('input[name="Presets[gamma_mode]"]:checked').val();
        if (mode === 'list') {
            $('.mode-step-fields').hide();
            $('.mode-list-fields').fadeIn();
        } else {
            $('.mode-list-fields').hide();
            $('.mode-step-fields').fadeIn();
        }
    }
    
    // Al cambio del radio
    $('input[name="Presets[gamma_mode]"]').on('change', updateGammaVisibility);
    
    // Al caricamento della pagina
    updateGammaVisibility();
JS;
$this->registerJs($js);
?>