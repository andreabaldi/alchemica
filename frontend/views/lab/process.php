<?php
use yii\helpers\Html;

$this->title = 'Configurazione Alchemica: ' . $preset->technique_name;
$this->params['breadcrumbs'][] = ['label' => 'Laboratorio', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="lab-process">
    <div class="jumbotron text-center bg-light p-5 rounded">
        <h1 class="display-4">🧪 Pronto per l'elaborazione</h1>
        <p class="lead">Stai per generare un negativo digitale basato sulla tecnica <strong><?= Html::encode($preset->technique_name) ?></strong>.</p>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">Dati Tecnici Preset</div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Gamma Base:</th>
                            <td><span class="badge bg-primary"><?= $preset->gamma_base ?></span></td>
                        </tr>
                        <tr>
                            <th>Step Incrementale:</th>
                            <td><?= $preset->gamma_step ?></td>
                        </tr>
                        <tr>
                            <th>Inchiostro (Ink Limit):</th>
                            <td><?= $preset->ink_limit ?>%</td>
                        </tr>
                        <tr>
                            <th>Carta:</th>
                            <td><?= $preset->paper_name ?: 'Non specificata' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white">File Focus Caricato</div>
                <div class="card-body text-center">
                    <p class="text-muted">Nome file: <code><?= Html::encode($fileName) ?></code></p>
                    <div class="py-3">
                        <i class="fas fa-file-image fa-4x text-secondary"></i>
                    </div>
                    <p>L'immagine verrà clonata 24 volte per creare la matrice di calibrazione.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5">
        <?= Html::a('🔬 Genera Matrice ' . ($invert ? 'NEGATIVA' : 'POSITIVA'),
                [
                        'generate-tiff',
                        'file' => $fileName,
                        'presetId' => $preset->id,
                        'invert' => $invert,      // <--- Fondamentale
                        'gridSize' => $gridSize   // <--- Fondamentale
                ],
                ['class' => 'btn btn-success btn-lg shadow', 'data-method' => 'post']
        ) ?>
    </div>