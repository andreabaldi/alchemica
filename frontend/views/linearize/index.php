<?php
/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'SCTV Linearizer - Alchemica Lab';
$this->registerJsFile('https://cdn.jsdelivr.net/npm/chart.js', ['position' => \yii\web\View::POS_HEAD]);
?>

    <div class="linearize-index" style="max-width: 1600px; margin: 0 auto; padding: 20px;">

        <div class="d-flex align-items-center mb-4">
            <img src="<?= Yii::$app->request->getBaseUrl(true); ?>/alchemica.jpg" style="width: 60px; margin-right: 15px;" />
            <h1 class="m-0" style="font-weight: 800;"><?= Html::encode($this->title) ?></h1>
        </div>

        <?php $form = ActiveForm::begin([
                'id' => 'sctv-active-form',
                'action' => Url::to(['linearize/generate-files']),
                'method' => 'post'
        ]); ?>

        <div class="card shadow-sm border-0 mb-4 bg-dark text-white p-3">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="small text-muted mb-1 text-uppercase">1. Carica CSV</label>
                    <input type="file" id="csv-file-input" class="form-control form-control-sm bg-secondary text-white border-0">
                </div>
                <div class="col-md-3">
                    <label class="small text-muted mb-1 text-uppercase">2. Nome Profilo</label>
                    <?= Html::textInput('lut_name', 'SCTV_LINEARIZATION', ['class' => 'form-control form-control-sm bg-secondary text-white border-0', 'id' => 'lut_name']) ?>
                </div>
                <div class="col-md-6 text-right">
                    <input type="hidden" id="json-output-data" name="output_data">
                    <button type="submit" class="btn btn-success font-weight-bold shadow-sm px-5">
                        GENERA PACCHETTO
                    </button>
                </div>
            </div>
        </div>

        <div class="row no-gutters">
            <div class="col-md-3 pr-2">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-header py-2 bg-secondary text-white small text-center font-weight-bold">INPUT LAB</div>
                    <div class="card-body p-2">
                        <textarea id="bulk-paste-area" class="form-control form-control-sm border-0 bg-white mb-2" rows="1" placeholder="Incolla L a b..."></textarea>
                        <table class="table table-sm table-borderless m-0">
                            <tbody>
                            <?php for ($i = 0; $i <= 100; $i += 5): ?>
                                <tr class="lab-row border-bottom">
                                    <td class="small font-weight-bold align-middle text-center" style="width:45px; background:#eee;"><?= $i ?>%<input type="hidden" class="patch-val" value="<?= $i ?>"></td>
                                    <td><?= Html::textInput("l[$i]", '', ['class' => 'form-control form-control-sm lab-i lab-l', 'placeholder' => 'L']) ?></td>
                                    <td><?= Html::textInput("a[$i]", '', ['class' => 'form-control form-control-sm lab-i lab-a', 'placeholder' => 'a']) ?></td>
                                    <td><?= Html::textInput("b[$i]", '', ['class' => 'form-control form-control-sm lab-i lab-b', 'placeholder' => 'b']) ?></td>
                                </tr>
                            <?php endfor; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6 px-2">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body p-3">
                        <div style="aspect-ratio: 1 / 1; width: 100%; position: relative;">
                            <canvas id="sctvChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 bg-dark text-warning p-3">
                    <div class="card shadow-sm border-0 bg-dark text-warning p-3">
                        <div class="row text-center">
                            <div class="col-4 border-right border-secondary">
                                <span class="small text-muted d-block text-uppercase">Massimo (Solid)</span>
                                <strong style="font-size: 1.2rem;">Min: <span id="info-white">-</span></strong>
                            </div>
                            <div class="col-4 border-right border-secondary">
                                <span class="small text-muted d-block text-uppercase">Massimo (White)</span>
                                <strong style="font-size: 1.2rem;">Max: <span id="info-solid">-</span></strong>
                            </div>
                            <div class="col-4">
                                <span class="small text-muted d-block text-uppercase">Dynamic Range</span>
                                <strong style="font-size: 1.2rem; color: #00ff00;"><span id="info-delta">-</span></strong><small> ΔE</small>
                            </div>
                        </div>

                        <div class="row mt-3 pt-2 border-top border-secondary">
                            <div class="col-12 text-left">
                                <p id="spiegazione-d" class="small text-white-50 m-0" style="font-style: italic;">
                                    Sotto  40   ⚠️ Gamma  ridotta: il contrasto è debole.<br>
                                    Tra 40 e 65    ✅ Gamma standard: contrasto adeguato      s. <br>
                                    Sopra  65 🌟 Ottima Gamma Ottima: neri profondi e massima separazione dei toni.<br>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3 pl-2">
                <div class="card shadow-sm border-0">
                    <div class="card-header py-2 bg-dark text-white small text-center font-weight-bold">OUTPUT SCTV</div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-striped text-center m-0">
                            <thead><tr class="small border-bottom"><th>TARGET</th><th>SCTV</th><th>CORR.</th></tr></thead>
                            <tbody id="output-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <style>
        .lab-i { height: 24px !important; font-size: 0.8rem !important; text-align: center; border: 1px solid #ddd; padding: 2px; }
        .text-orange { color: #fd7e14 !important; font-weight: bold; }
        #output-table-body td { padding: 6px 2px; font-size: 0.85rem; }
    </style>

<?php
$js = <<<JS
    var ctx = document.getElementById('sctvChart').getContext('2d');
    var chart;

    function initChart() {
        chart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [
                    { label: 'Your Print', borderColor: '#007bff', borderWidth: 2.5, data: [], fill: false },
                    { label: 'Correction', borderColor: '#fd7e14', backgroundColor: 'rgba(253, 126, 20, 0.1)', data: [], fill: true },
                    { label: 'Ideal', borderColor: '#28a745', borderDash: [5,5], data: [{x:0,y:0},{x:100,y:100}], fill: false, pointRadius: 0 }
                ]
            },
            options: { 
                responsive: true, maintainAspectRatio: false,
                scales: { x: { type: 'linear', min: 0, max: 110 }, y: { type: 'linear', min: 0, max: 110 } }
            }
        });
    }

    function invert(target, data) {
        let s = [...data].sort((a, b) => a.y - b.y);
        for (let i = 0; i < s.length - 1; i++) {
            if (target >= s[i].y && target <= s[i+1].y) {
                return s[i].x + (target - s[i].y) / (s[i+1].y - s[i].y) * (s[i+1].x - s[i].x);
            }
        }
        return target;
    }

    function update() {
        let rows = $('.lab-row');
        let l0 = rows.first().find('.lab-l').val(), l100 = rows.last().find('.lab-l').val();
        if (!l0 || !l100) return;

        let pL = parseFloat(l0), pA = parseFloat(rows.first().find('.lab-a').val()), pB = parseFloat(rows.first().find('.lab-b').val());
        let sL = parseFloat(l100), sA = parseFloat(rows.last().find('.lab-a').val()), sB = parseFloat(rows.last().find('.lab-b').val());

        // Update Pannello Nero
        
        let dE = Math.sqrt(Math.pow(pL-sL, 2) + Math.pow(pA-sA, 2) + Math.pow(pB-sB, 2)).toFixed(2);
        $('#info-white').text(pL); $('#info-solid').text(sL); $('#info-delta').text(dE);

        let den = Math.pow(pL - sL, 2) + Math.pow(pA - sA, 2) + Math.pow(pB - sB, 2);
        let measured = [];
        rows.each(function() {
            let x = parseInt($(this).find('.patch-val').val());
            let l = parseFloat($(this).find('.lab-l').val()) || 0, a = parseFloat($(this).find('.lab-a').val()) || 0, b = parseFloat($(this).find('.lab-b').val()) || 0;
            let sctv = (den <= 0) ? x : Math.sqrt((Math.pow(pL - l, 2) + Math.pow(pA - a, 2) + Math.pow(pB - b, 2)) / den) * 100;
            measured.push({ x: x, y: sctv });
        });
        
        

        let html = ""; let corrObj = {}; let correction = [];
        for (let i = 0; i <= 100; i += 5) {
            let yCorr = (i === 0) ? 0 : (i === 100) ? 100 : invert(i, measured);
            correction.push({ x: i, y: yCorr });
            corrObj[i] = yCorr;
            let mVal = measured.find(p => p.x === i).y;
            html += `<tr><td>\${i}%</td><td>\${Math.round(mVal)}%</td><td class="text-orange">\${Math.round(yCorr)}%</td></tr>`;
        }
        chart.data.datasets[0].data = measured; chart.data.datasets[1].data = correction; chart.update();
        $('#output-table-body').html(html); $('#json-output-data').val(JSON.stringify(corrObj));
    }

    $('#csv-file-input').on('change', function(e) {
        let reader = new FileReader();
        reader.onload = function(ev) {
            let lines = ev.target.result.split(/\\r?\\n/).filter(l => l.trim() !== "");
            let sep = lines[0].includes(';') ? ';' : ',';
            let header = lines[0].split(sep).map(h => h.trim().toUpperCase());
            let idxL = header.indexOf('CIE L'), idxA = header.indexOf('CIE A'), idxB = header.indexOf('CIE B');
            if (idxL === -1) { idxL = 10; idxA = 11; idxB = 12; }
            $('.lab-row').each(function(index) {
                let d = lines[index + 1] ? lines[index + 1].split(sep) : null;
                if (d && d[idxL] !== undefined) {
                    $(this).find('.lab-l').val(d[idxL].trim().replace(',','.'));
                    $(this).find('.lab-a').val(d[idxA].trim().replace(',','.'));
                    $(this).find('.lab-b').val(d[idxB].trim().replace(',','.'));
                }
            });
            update();
        };
        reader.readAsText(e.target.files[0]);
    });

    $('#bulk-paste-area').on('paste', function(e) {
        let pasteData = (e.originalEvent || e).clipboardData.getData('text');
        let lines = pasteData.split(/\\r?\\n/).filter(l => l.trim() !== "");
        lines.forEach((line, i) => {
            let cols = line.split(/[\\t\\s,;]+/).filter(c => c.trim() !== "");
            if ($('.lab-row').eq(i).length && cols.length >= 3) {
                $('.lab-row').eq(i).find('.lab-l').val(cols[0].replace(',','.'));
                $('.lab-row').eq(i).find('.lab-a').val(cols[1].replace(',','.'));
                $('.lab-row').eq(i).find('.lab-b').val(cols[2].replace(',','.'));
            }
        });
        setTimeout(update, 100); setTimeout(() => { $(this).val(''); }, 200);
    });

    $(document).on('input', '.lab-i', update);
    initChart();
JS;
$this->registerJs($js);
?>