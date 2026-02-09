<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var \common\models\Presets $model */

$this->title = $model->technique_name;
$this->params['breadcrumbs'][] = ['label' => 'Presets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="presets-view">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-microscope text-muted mr-2"></i> <?= Html::encode($this->title) ?></h1>
        <p class="mb-0">
            <?= Html::a('<i class="fas fa-edit"></i> Modifica', ['update', 'id' => $model->id], ['class' => 'btn btn-primary shadow-sm']) ?>
            <?= Html::a('<i class="fas fa-trash"></i> Elimina', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-outline-danger',
                    'data' => [
                            'confirm' => 'Sei sicuro di voler eliminare questo preset?',
                            'method' => 'post',
                    ],
            ]) ?>
        </p>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-dark text-white font-weight-bold" style="border-radius: 12px 12px 0 0;">
                    Informazioni Base
                </div>
                <div class="card-body p-0">
                    <?= DetailView::widget([
                            'model' => $model,
                            'options' => ['class' => 'table mb-0'],
                            'attributes' => [
                                    [
                                            'attribute' => 'id',
                                            'captionOptions' => ['style' => 'width: 40%; background: #fdfdfd;'],
                                    ],
                                    'technique_name',
                                    'paper_name',
                                    [
                                            'attribute' => 'color_hex',
                                            'format' => 'raw',
                                            'value' => function($model) {
                                                return '<span class="badge" style="background-color:'.$model->color_hex.'; width:20px; height:20px; display:inline-block; vertical-align:middle; border:1px solid #ddd; margin-right:8px;"></span>' . $model->color_hex;
                                            }
                                    ],
                            ],
                    ]) ?>
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-warning text-dark font-weight-bold" style="border-radius: 12px 12px 0 0;">
                    Parametri Esposizione
                </div>
                <div class="card-body p-0">
                    <?= DetailView::widget([
                            'model' => $model,
                            'options' => ['class' => 'table mb-0'],
                            'attributes' => [
                                    [
                                            'attribute' => 'uv_exposure_seconds',
                                            'value' => $model->uv_exposure_seconds ? $model->uv_exposure_seconds . ' sec' : '-',
                                    ],
                                    [
                                            'attribute' => 'ink_limit',
                                            'value' => $model->ink_limit . '%',
                                    ],
                            ],
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-primary text-white font-weight-bold" style="border-radius: 12px 12px 0 0;">
                    Logica di Generazione Gamma
                </div>
                <div class="card-body bg-white p-4">
                    <?php if ($model->gamma_mode === 'list'): ?>
                        <h6 class="text-muted text-uppercase small font-weight-bold">Valori Gamma Selezionati (Lista)</h6>
                        <div class="mt-2">
                            <?php
                            $gammas = is_array($model->gamma_custom_list) ? $model->gamma_custom_list : explode(',', $model->gamma_custom_list);
                            foreach ($gammas as $g): ?>
                                <span class="badge badge-pill badge-light border text-dark p-2 px-3 mr-1 mb-2" style="font-size: 1.1rem;">
                                        <i class="fas fa-adjust text-primary mr-1"></i> <?= trim($g) ?>
                                    </span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="row text-center">
                            <div class="col-6 border-right">
                                <label class="text-muted small d-block">GAMMA BASE</label>
                                <span class="h3 font-weight-bold"><?= $model->gamma_base ?></span>
                            </div>
                            <div class="col-6">
                                <label class="text-muted small d-block">GAMMA STEP</label>
                                <span class="h3 font-weight-bold text-info">+ <?= $model->gamma_step ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-light border-0 py-2 text-center">
                    <small class="text-muted">Modalità attuale: <strong><?= strtoupper($model->gamma_mode ?: 'step') ?></strong></small>
                </div>
            </div>

            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white font-weight-bold border-bottom">
                    Note di Sviluppo
                </div>
                <div class="card-body bg-light">
                    <p class="mb-0 text-dark" style="white-space: pre-line;">
                        <?= $model->notes ?: '<span class="text-muted font-italic">Nessuna nota aggiuntiva</span>' ?>
                    </p>
                </div>
            </div>

            <div class="mt-3 text-right">
                <small class="text-muted">Creato: <?= Yii::$app->formatter->asDatetime($model->created_at) ?> | Aggiornato: <?= Yii::$app->formatter->asDatetime($model->updated_at) ?></small>
            </div>
        </div>
    </div>
</div>

<style>
    .table th { color: #6c757d; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .table td { font-weight: 500; }
    .card { transition: all 0.3s ease; }
    .card:hover { transform: translateY(-2px); }
</style>