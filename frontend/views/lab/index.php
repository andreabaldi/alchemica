<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\switchinput\SwitchInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var frontend\models\TargetUploadForm $model */
/** @var common\models\Presets[] $presets */
/** @var array $lutList */
/** @var string|null $resultFile */
/** @var string|null $previewFile */

$this->title = 'Alchemica Lab - Digital Negative Processor';
$session = Yii::$app->session;
$lastFile = $session->get('last_uploaded_file');
?>

<div class="lab-index container-fluid py-4 bg-light min-vh-100">

    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 bg-white p-3 rounded shadow-sm">
        <div>
            <h1 class="display-5 fw-bold text-dark mb-0">
                <span class="text-primary"><i class="fas fa-flask"></i> ALchemica </span><span class="fw-light">Lab</span>
            </h1>
            <p class="text-muted mb-0 text-uppercase tracking-widest small" style="letter-spacing: 2px;">High-Precision Engine v3.0</p>
        </div>

        <div class="d-flex align-items-center">
            <div class="btn-group shadow-sm me-3">
                <?= Html::a('<i class="fas fa-folder-open me-2"></i> ARCHIVIO FILE', ['lab/file-manager'], [
                        'class' => 'btn btn-outline-primary btn-sm fw-bold px-3',
                ]) ?>
            </div>
            <span class="badge bg-dark p-2 px-3 d-none d-md-inline-block">2026 EDITION</span>
        </div>
    </div>

    <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data', 'class' => 'animate__animated animate__fadeIn']
    ]); ?>

    <div class="row g-4">
        <div class="col-xl-4 col-lg-6">
            <div class="card h-100 border-0 shadow-lg overflow-hidden">
                <div class="card-header bg-secondary text-white py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-file-import me-2"></i> 1. Input & Tecnica</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4 p-3 border rounded bg-white shadow-sm">
                        <?= $form->field($model, 'imageFile')->fileInput(['class' => 'form-control'])->label('Sorgente Immagine') ?>
                    </div>

                    <div class="mb-4">
                        <?= $form->field($model, 'presetId')->widget(Select2::class, [
                                'data' => ArrayHelper::map($presets, 'id', 'technique_name'),
                                'options' => [
                                        'id' => 'preset-selector',
                                        'placeholder' => 'Scegli la tecnica...'
                                ],
                                'pluginOptions' => ['allowClear' => true]
                        ])->label('Preset Sviluppo (Gamma)') ?>

                        <div id="gamma-info-box" class="mt-2 p-3 border rounded shadow-sm bg-white <?= (!isset($currentPreset) && !isset($analysis)) ? 'd-none' : '' ?>">
                            <div class="row align-items-center g-2">
                                <div class="col-12 mb-1 border-bottom pb-1 d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-uppercase text-muted fw-bold" style="font-size: 0.65rem;">Profilo Tecnico:</small>
                                        <span id="info-profile-name" class="fw-bold text-dark ms-1 small"><?= isset($currentPreset) ? $currentPreset->technique_name : '---' ?></span>
                                    </div>
                                    <span id="info-mode-badge" class="badge bg-secondary" style="font-size: 0.6rem;">---</span>
                                </div>

                                <div id="area-step" class="row g-0 w-100">
                                    <div class="col-4 text-center border-end">
                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Γ Immagine</small>
                                        <span id="gamma-img-val" class="fw-bold text-primary small"><?= isset($analysis) ? number_format($analysis['gamma'], 2) : '---' ?></span>
                                    </div>
                                    <div class="col-4 text-center border-end">
                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Γ Base</small>
                                        <span id="info-gamma-base" class="fw-bold text-dark small"><?= isset($currentPreset) ? number_format($currentPreset->gamma_base, 2) : '0.00' ?></span>
                                    </div>
                                    <div class="col-4 text-center">
                                        <small class="text-muted d-block" style="font-size: 0.65rem;">Δ Delta</small>
                                        <span id="info-gamma-delta" class="fw-bold text-success small">+<?= isset($currentPreset) ? number_format($currentPreset->gamma_step, 2) : '0.00' ?></span>
                                    </div>
                                </div>

                                <div id="area-list" class="col-12 d-none">
                                    <small class="text-muted d-block mb-1" style="font-size: 0.65rem;">Gamma Custom attivi:</small>
                                    <div id="info-gamma-list-tags" class="d-flex flex-wrap gap-1">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-warning bg-opacity-10 border border-warning rounded text-center">
                        <small class="text-muted fw-bold text-uppercase d-block mb-2">Inversione Polarità</small>
                        <?= $form->field($model, 'invert')->widget(SwitchInput::class, [
                                'pluginOptions' => [
                                        'onText' => 'NEG', 'offText' => 'POS',
                                        'onColor' => 'dark', 'offColor' => 'primary',
                                ]
                        ])->label(false) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-6">
            <div class="card h-100 border-0 shadow-lg border-start border-primary border-5">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-microchip me-2"></i> 2. Calibrazione UV</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="fw-bold text-primary">Correzione LUT</label>
                        <?= $form->field($model, 'applyLut')->widget(SwitchInput::class, [
                                'pluginOptions' => ['size' => 'small', 'onColor' => 'success']
                        ])->label(false) ?>
                    </div>

                    <?= $form->field($model, 'lutFile')->widget(Select2::class, [
                            'data' => $lutList ?? [],
                            'options' => ['placeholder' => 'Seleziona file .cube...'],
                            'pluginOptions' => ['allowClear' => true]
                    ])->label(false) ?>

                    <hr class="my-4">

                    <div class="mb-3 p-2 border rounded bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="small fw-bold text-uppercase">Mirror (Flip Orizzontale)</label>
                            <?= $form->field($model, 'mirrorImage')->widget(SwitchInput::class, [
                                    'pluginOptions' => [
                                            'size' => 'mini', 'onText' => 'MIR', 'offText' => 'OFF', 'onColor' => 'warning'
                                    ]
                            ])->label(false) ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?= $form->field($model, 'addStepWedge')->widget(SwitchInput::class, [
                                'pluginOptions' => ['onText' => 'ON', 'offText' => 'OFF', 'onColor' => 'info']
                        ])->label('Includi Stouffer Digitale') ?>
                    </div>

                    <?= $form->field($model, 'steps')->dropDownList([
                            10 => '10 Step (Rapido)',
                            21 => '21 Step (Standard Stouffer)',
                            31 => '31 Step (Alta Precisione)'
                    ], ['class' => 'form-select form-select-sm mb-3'])->label('Risoluzione Scala') ?>

                    <div class="mb-3 p-3 border rounded bg-white shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="fw-bold text-uppercase text-info">Numeri Stepwedge</small>
                            <?= $form->field($model, 'wedgeNumbers')->widget(SwitchInput::class, [
                                    'pluginOptions' => ['size' => 'mini', 'onColor' => 'info']
                            ])->label(false) ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="fw-bold text-uppercase text-dark">Mire di Registro</small>
                            <?= $form->field($model, 'addRegMarks')->widget(SwitchInput::class, [
                                    'pluginOptions' => ['size' => 'mini', 'onColor' => 'dark']
                            ])->label(false) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'keepFirstOriginal')->widget(SwitchInput::class, [
                            'pluginOptions' => ['onColor' => 'warning']
                    ])->label('Cella 1: Controllo (No Gamma)') ?>

                    <div class="d-grid mt-4">
                        <?= Html::submitButton('<i class="fas fa-vial me-2"></i> GENERA STRIP 5x5 (UV MASK)', [
                                'class' => 'btn btn-outline-primary btn-sm fw-bold',
                                'formaction' => Url::to(['lab/generate-lut-test']),
                                'name' => 'submit-lut-test'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-12">
            <div class="card h-100 border-0 shadow-lg">
                <div class="card-header bg-dark text-white py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-layer-group me-2"></i> 3. Layout & Dimensioni</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-6">
                            <?= $form->field($model, 'paperFormat')->widget(Select2::class, [
                                    'data' => ['A4' => 'A4', 'A3' => 'A3', 'A3+' => 'A3 Plus', 'Letter' => 'LTR'],
                                    'pluginOptions' => ['minimumResultsForSearch' => -1]
                            ]) ?>
                        </div>
                        <div class="col-6">
                            <?= $form->field($model, 'orientation')->widget(Select2::class, [
                                    'data' => ['auto' => 'AUTO', 'portrait' => 'PORTRAIT', 'landscape' => 'LANDSCAPE'],
                                    'pluginOptions' => ['minimumResultsForSearch' => -1]
                            ]) ?>
                        </div>
                    </div>

                    <?= $form->field($model, 'gridSize')->dropDownList([
                            1 => '1 Immagine (Full Page)',
                            2 => '2 Immagini (Side by Side)',
                            4 => '4 Immagini (2x2 Grid)',
                            6 => '6 Immagini (2x3 Grid)',
                            9 => '9 Immagini (3x3 Grid)',
                            12 => '12 Immagini (3x4 Grid)',
                            16 => '16 Immagini (4x4 Grid)'
                    ], ['class' => 'form-select form-select-lg mt-2 text-center']) ?>

                    <div class="d-grid gap-2 mt-5">
                        <?= Html::submitButton('<i class="fas fa-rocket me-2"></i> GENERA MATRICE', [
                                'class' => 'btn btn-success btn-lg py-3 shadow-sm fw-bold text-uppercase'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <?php if ($previewFile): ?>
        <div class="mt-5 pt-4 animate__animated animate__fadeInUp text-center">
            <h2 class="fw-bold text-secondary text-uppercase mb-4">Render Finale</h2>
            <div class="bg-white p-3 shadow-2xl border rounded d-inline-block">
                <div class="preview-frame shadow-sm mb-3">
                    <?= Html::img("@web/uploads/targets/{$previewFile}", [
                            'class' => 'img-fluid',
                            'style' => 'max-height: 70vh; width: auto;'
                    ]) ?>
                </div>
                <div class="mt-2">
                    <?= Html::a('<i class="fas fa-cloud-download-alt me-2"></i> DOWNLOAD MASTER TIFF 16-BIT',
                            ['download', 'name' => $resultFile],
                            ['class' => 'btn btn-primary btn-lg px-5 py-3 shadow-lg fw-bold']) ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Preparazione Mappa Preset per il Javascript
$presetsMap = [];
foreach ($presets as $p) {
    $presetsMap[$p->id] = [
            'name' => $p->technique_name,
            'mode' => $p->gamma_mode,
            'base' => number_format($p->gamma_base, 2),
            'delta' => number_format($p->gamma_step, 2),
            'list' => $p->gamma_custom_list // afterFind lo ha già reso array
    ];
}
$presetsJson = json_encode($presetsMap);

$this->registerJs("
    const presetsData = {$presetsJson};
    const gridSizeSelect = $('#targetuploadform-gridsize');

    function updateProcessingUI() {
        const id = $('#preset-selector').val();
        
        if (!id || !presetsData[id]) {
            $('#gamma-info-box').addClass('d-none');
            gridSizeSelect.prop('disabled', false).parent().css('opacity', '1');
            return;
        }

        const data = presetsData[id];
        $('#gamma-info-box').removeClass('d-none');
        $('#info-profile-name').text(data.name);
        $('#info-mode-badge').text(data.mode.toUpperCase());

        if (data.mode === 'list') {
            // --- MODALITÀ LISTA ---
            $('#area-step').addClass('d-none');
            $('#area-list').removeClass('d-none');
            
            gridSizeSelect.prop('disabled', true).parent().css('opacity', '0.5');
            
            let tags = '';
            if (data.list && data.list.length > 0) {
                data.list.forEach(val => {
                    // FIX: Uso la concatenazione classica invece del template literal con $
                    tags += '<span class=\"badge bg-primary\" style=\"font-size:0.7rem\">' + parseFloat(val).toFixed(2) + '</span> ';
                });
            } else {
                tags = '<span class=\"text-danger small\">Lista vuota!</span>';
            }
            $('#info-gamma-list-tags').html(tags);

        } else {
            // --- MODALITÀ STEP ---
            $('#area-list').addClass('d-none');
            $('#area-step').removeClass('d-none');
            
            gridSizeSelect.prop('disabled', false).parent().css('opacity', '1');
            
            $('#info-gamma-base').text(data.base);
            $('#info-gamma-delta').text('+' + data.delta);
        }
    }

    updateProcessingUI();

    $('#preset-selector').on('change', function() {
        updateProcessingUI();
    });
", \yii\web\View::POS_READY);
?>

<style>
    body { background-color: #f0f2f5; font-family: 'Inter', sans-serif; }
    .card { border-radius: 15px; border: none; }
    .btn-success { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border: none; }
    .btn-primary { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border: none; }
    .preview-frame { border: 12px solid #f8f9fa; background: #fff; padding: 10px; border-radius: 4px; }
    .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); }
    #gamma-info-box { transition: all 0.3s ease; }
    .badge { letter-spacing: 0.5px; }
</style>