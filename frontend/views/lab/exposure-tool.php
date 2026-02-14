<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * Alchemica Lab - Exposure Engine v3.5
 * Integrates: Multi-zone Caliper, Time/Units toggles, Visual Themes.
 */
?>

    <div class="alchemica-pro-tool container-fluid py-3" style="background:#e9ecef; min-height:100vh; font-family: 'Segoe UI', system-ui, sans-serif;">
        <div class="row justify-content-center">

            <div class="col-md-7">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                        <span class="fw-bold tracking-wider">ALCHEMICA LAB | ENGINE v3.5</span>
                        <div class="d-flex align-items-center gap-2">
                            <small class="text-uppercase" style="font-size:10px; opacity:0.7;">Scale Visual Style:</small>
                            <select id="process-color" class="form-select form-select-sm bg-dark text-white border-secondary" style="width:auto;">
                                <option value="bw">Neutral B&W</option>
                                <option value="cyan">Prussian Blue (Ciano)</option>
                                <option value="vdb">VDB Sepia</option>
                                <option value="pal">Palladium Deep</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <?php $form = ActiveForm::begin(['id' => 'stouffer-form']); ?>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <?= $form->field($model, 'wedgeType')->dropDownList([31 => 'Scale 31 (1/3 stop)', 21 => 'Scale 21 (1/2 stop)'], ['id' => 'wedge-type'])->label('Physical Wedge') ?>
                            </div>
                            <div class="col-md-4">
                                <?= $form->field($model, 'inputType')->dropDownList(['units' => 'UNITS', 'time' => 'TIME (m:s)'], ['id' => 'input-type'])->label('Format') ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Base Exposure</label>

                                <div id="input-units-wrap" class="input-group shadow-sm">
                                    <button type="button" class="btn btn-dark btn-adj" data-target="baseunits" data-val="-50">-</button>
                                    <?= Html::activeTextInput($model, 'baseUnits', ['id' => 'exposurecalculator-baseunits', 'class' => 'form-control text-center fw-bold']) ?>
                                    <button type="button" class="btn btn-dark btn-adj" data-target="baseunits" data-val="50">+</button>
                                </div>

                                <div id="input-time-wrap" class="d-none">
                                    <div class="d-flex flex-column gap-2">
                                        <div class="input-group input-group-sm shadow-sm">
                                            <button type="button" class="btn btn-dark btn-adj" data-target="baseminutes" data-val="-1">-</button>
                                            <?= Html::activeTextInput($model, 'baseMinutes', ['id' => 'exposurecalculator-baseminutes', 'class' => 'form-control text-center fw-bold']) ?>
                                            <button type="button" class="btn btn-dark btn-adj" data-target="baseminutes" data-val="1">+</button>
                                            <span class="input-group-text" style="width:40px">m</span>
                                        </div>
                                        <div class="input-group input-group-sm shadow-sm">
                                            <button type="button" class="btn btn-dark btn-adj" data-target="baseseconds" data-val="-5">-5</button>
                                            <?= Html::activeTextInput($model, 'baseSeconds', ['id' => 'exposurecalculator-baseseconds', 'class' => 'form-control text-center']) ?>
                                            <button type="button" class="btn btn-dark btn-adj" data-target="baseseconds" data-val="5">+5</button>
                                            <span class="input-group-text" style="width:40px">s</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="p-3 bg-white border border-2 rounded text-center shadow-sm">
                                    <small class="text-muted d-block fw-bold" style="font-size:10px">DMAX (BLACK)</small>
                                    <span id="lbl-dmax" class="h2 fw-bold text-dark">1</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 bg-white border border-2 rounded text-center shadow-sm">
                                    <small class="text-muted d-block fw-bold" style="font-size:10px">DMIN (WHITE)</small>
                                    <span id="lbl-dmin" class="h2 fw-bold text-dark">31</span>
                                </div>
                            </div>
                        </div>

                        <?= Html::activeHiddenInput($model, 'dmaxStep', ['id' => 'hid-dmax']) ?>
                        <?= Html::activeHiddenInput($model, 'dminStep', ['id' => 'hid-dmin']) ?>
                        <?= Html::button('CALCULATE EXPOSURE', ['class' => 'btn btn-dark w-100 py-3 fw-bold tracking-wider shadow', 'id' => 'btn-calc']) ?>
                        <?php ActiveForm::end(); ?>
                    </div>
                </div>

                <div id="res-panel" class="card border-0 shadow-sm overflow-hidden" style="transition: opacity 0.3s ease;">
                    <div id="res-header" class="p-4 text-center transition-color theme-bw">
                        <h6 class="text-uppercase mb-1 opacity-75 fw-bold">Optimal Value</h6>
                        <div id="res-val" class="display-1 fw-bold font-monospace">--:--</div>
                    </div>
                    <div class="p-4 bg-white border-top">
                        <div class="row text-center">
                            <div class="col-4 border-end">
                                <small class="text-muted d-block">EV VAR.</small>
                                <strong id="res-stops" class="h4">--</strong>
                            </div>
                            <div class="col-4 border-end">
                                <small class="text-muted d-block">SCALE STEP</small>
                                <strong id="res-fraction" class="h4">--</strong>
                            </div>
                            <div class="col-4">
                                <small class="text-muted d-block">SHIFT</small>
                                <strong id="res-steps-info" class="h4">--</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="alert bg-white shadow-sm border-start border-info border-4 mb-3 py-2 px-3">
                    <small class="text-info fw-bold d-block mb-1 text-uppercase" style="letter-spacing: 1px;">
                        <i class="fas fa-mouse-pointer me-1"></i> Interactive Guide
                    </small>
                    <ul class="list-unstyled mb-0" style="font-size: 0.75rem; color: #444; line-height: 1.4;">
                        <li><i class="fas fa-arrows-alt-v fa-fw text-muted"></i> <strong>Drag Top/Bottom:</strong> Set DMAX & DMIN.</li>
                        <li><i class="fas fa-hand-rock fa-fw text-muted"></i> <strong>Drag Center:</strong> Move the entire scale.</li>
                    </ul>
                </div>
                <div class="wedge-container bg-white shadow-sm p-4 rounded border d-flex justify-content-center">
                    <div class="wedge-flex-box d-flex">
                        <div id="labels-container" class="d-flex flex-column text-end"></div>
                        <div id="wedge-track" class="ms-3">
                            <div id="caliper-box">
                                <div class="zone top-zone"></div>
                                <div class="zone mid-zone"></div>
                                <div class="zone btm-zone"></div>
                            </div>
                            <div id="segments-container"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .wedge-flex-box { display: flex; align-items: flex-start; }
        #wedge-track { width: 65px; border: 2px solid #333; position: relative; cursor: crosshair; user-select: none; background: #fff; }
        .w-segment { height: 18px; width: 65px; border-bottom: 1px solid rgba(0,0,0,0.05); }
        .w-num { height: 18px; font-size: 11px; font-family: 'Courier New', monospace; line-height: 18px; width: 35px; font-weight: bold; color: #999; }

        /* Caliper Style (Neutral) */
        #caliper-box {
            position: absolute; left: -8px; width: 81px;
            border: 2px solid #000; z-index: 10; display: flex; flex-direction: column;
            background: rgba(255,255,255,0.1);
        }
        .zone { width: 100%; }
        .top-zone { height: 12px; background: #222; cursor: ns-resize; border-bottom: 1px solid #fff; }
        .mid-zone { flex-grow: 1; cursor: grab; }
        .btm-zone { height: 12px; background: #fff; border-top: 2px solid #222; cursor: ns-resize; }

        /* Color Themes */
        .theme-bw { background: #1a1a1a !important; color: #fff !important; }
        .theme-cyan { background: #003153 !important; color: #fff !important; }
        .theme-vdb { background: #3e2723 !important; color: #fff !important; }
        .theme-pal { background: #231f20 !important; color: #d7ccc8 !important; border-bottom: 4px solid #3e2723; }

        .display-1 { font-size: 5rem; letter-spacing: -2px; }
        .btn-adj { font-weight: bold; min-width: 40px; }
        .transition-color { transition: all 0.4s ease; }
    </style>

<?php
$js = <<<JS
let dmax = 1; let dmin = 31;
let isDragging = null; let startY, startDmax, startDmin;

const themes = {
    bw:   { r: 0,   g: 0,   b: 0,   css: 'theme-bw' },
    cyan: { r: 0,   g: 49,  b: 83,  css: 'theme-cyan' }, 
    vdb:  { r: 62,  g: 39,  b: 35,  css: 'theme-vdb' },
    pal:  { r: 35,  g: 31,  b: 32,  css: 'theme-pal' } 
};

function resetResults() {
    $('#res-val').text('--:--');
    $('#res-stops, #res-fraction, #res-steps-info').text('--');
    $('#res-panel').css('opacity', '0.5');
}

function buildWedge() {
    let steps = parseInt($('#wedge-type').val());
    let hL = ''; let hS = '';
    for(let i=1; i<=steps; i++) {
        hL += `<div class="w-num">\${i}</div>`;
        hS += `<div class="w-segment" data-i="\${i}"></div>`;
    }
    $('#labels-container').html(hL); 
    $('#segments-container').html(hS);
    if(dmin > steps) dmin = steps;
    updateScale();
}

function updateScale() {
    let t = themes[$('#process-color').val()];
    $('#lbl-dmax').text(dmax); $('#lbl-dmin').text(dmin);
    $('#hid-dmax').val(dmax); $('#hid-dmin').val(dmin);
    
    $('#caliper-box').css({ 'top': (dmax - 1) * 18 + 'px', 'height': (dmin - dmax + 1) * 18 + 'px' });

    $('.w-segment').each(function() {
        let i = $(this).data('i');
        if (i <= dmax) $(this).css('background-color', `rgb(\${t.r},\${t.g},\${t.b})`);
        else if (i >= dmin) $(this).css('background-color', '#fff');
        else {
            let ratio = (i - dmax) / (dmin - dmax);
            let r = Math.round(t.r + (255 - t.r) * ratio);
            let g = Math.round(t.g + (255 - t.g) * ratio);
            let b = Math.round(t.b + (255 - t.b) * ratio);
            $(this).css('background-color', `rgb(\${r},\${g},\${b})`);
        }
    });
    $('#res-header').removeClass('theme-bw theme-cyan theme-vdb theme-pal').addClass(t.css);
}

$(document).on('mousedown', '.zone', function(e) {
    isDragging = $(this).attr('class').split(' ')[1];
    startY = e.pageY; startDmax = dmax; startDmin = dmin;
    e.preventDefault();
});

$(document).on('mousemove', function(e) {
    if (!isDragging) return;
    let s = parseInt($('#wedge-type').val());
    let delta = Math.round((e.pageY - startY) / 18);
    
    if (isDragging === 'top-zone') dmax = Math.max(1, Math.min(startDmax + delta, dmin - 1));
    else if (isDragging === 'btm-zone') dmin = Math.max(dmax + 1, Math.min(startDmin + delta, s));
    else {
        let diff = dmin - dmax;
        dmax = Math.max(1, Math.min(startDmax + delta, s - diff));
        dmin = dmax + diff;
    }
    updateScale();
});

$(document).on('mouseup', function() { isDragging = null; });

$('.btn-adj').click(function() {
    let target = $(this).data('target');
    let input = $('input[id$="' + target + '"]');
    let val = parseFloat(input.val()) || 0;
    let newVal = val + parseFloat($(this).data('val'));
    if (target === 'baseseconds') {
        if (newVal < 0) newVal = 55;
        if (newVal >= 60) newVal = 0;
    }
    input.val(newVal);
});

$('#wedge-type').on('change', function() { resetResults(); buildWedge(); });
$('#process-color').on('change', updateScale);
$('#input-type').change(function() {
    if ($(this).val() === 'time') { $('#input-time-wrap').removeClass('d-none'); $('#input-units-wrap').addClass('d-none'); }
    else { $('#input-time-wrap').addClass('d-none'); $('#input-units-wrap').removeClass('d-none'); }
});

$('#btn-calc').click(function() {
    $.post(window.location.href, $('#stouffer-form').serialize(), function(r) {
        if(r.success) {
            $('#res-panel').css('opacity', '1');
            $('#res-val').text(r.formatted);
            $('#res-stops').text('-' + r.stops + ' EV');
            $('#res-fraction').text(r.fractionLabel);
            $('#res-steps-info').text(r.diffSteps + ' Steps');
        }
    });
});

buildWedge();
JS;
$this->registerJs($js);
?>