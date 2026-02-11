<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <h4 class="mb-0">Gestione Archivio LUT</h4>
            <?= Html::a('Torna al Lab', ['lab/index'], ['class' => 'btn btn-sm btn-outline-light']) ?>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin(['action' => ['lut/upload'], 'options' => ['enctype' => 'multipart/form-data']]); ?>
            <div class="input-group mb-4">
                <input type="file" name="lutFile" class="form-control" accept=".png,.acv,.cube">
                <button class="btn btn-primary" type="submit">Carica Nuova LUT</button>
            </div>
            <?php ActiveForm::end(); ?>

            <table class="table table-hover">
                <thead class="table-light">
                <tr>
                    <th>Nome File</th>
                    <th>Tipo</th>
                    <th>Data</th>
                    <th class="text-end">Azioni</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($files as $file):
                    $name = basename($file); ?>
                    <tr>
                        <td><strong><?= $name ?></strong></td>
                        <td><span class="badge bg-info"><?= pathinfo($file, PATHINFO_EXTENSION) ?></span></td>
                        <td class="small text-muted"><?= date("d/m/Y H:i", filemtime($file)) ?></td>
                        <td class="text-end">
                            <?= Html::a('<i class="fas fa-trash"></i>', ['delete', 'name' => $name], [
                                    'class' => 'btn btn-danger btn-sm',
                                    'data-confirm' => 'Sei sicuro di voler eliminare questo file?'
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>