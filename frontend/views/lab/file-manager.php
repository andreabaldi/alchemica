<?php
use yii\helpers\Html;
?>
<div class="container py-4">
    <div class="d-flex justify-content-between mb-4">
        <h2><i class="fas fa-folder-open text-primary"></i> Gestione Target Generati</h2>
        <?= Html::a('Torna alla Dashboard', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?= Html::beginForm() ?>
    <div class="table-responsive bg-white shadow-sm rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
            <tr>
                <th width="40"><?= Html::checkbox('check_all', false, ['id' => 'check-all']) ?></th>
                <th>Anteprima</th>
                <th>Nome File</th>
                <th>Dimensione</th>
                <th>Data Creazione</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($files as $file): ?>
                <tr>
                    <td><?= Html::checkbox('selection[]', false, ['value' => $file['name']]) ?></td>
                    <td>
                        <img src="/uploads/targets/<?= $file['name'] ?>" style="height: 50px; width: 50px; object-fit: cover;" class="rounded border">
                    </td>
                    <td class="font-monospace small"><?= $file['name'] ?></td>
                    <td><?= $file['size'] ?></td>
                    <td><?= $file['date'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        <?= Html::submitButton('<i class="fas fa-trash"></i> Elimina selezionati', [
                'class' => 'btn btn-danger',
                'data-confirm' => 'Sei sicuro di voler eliminare i file selezionati?'
        ]) ?>
    </div>
    <?= Html::endForm() ?>
</div>

<script>
    document.getElementById('check-all').onclick = function() {
        var checkboxes = document.getElementsByName('selection[]');
        for (var checkbox of checkboxes) { checkbox.checked = this.checked; }
    }
</script>