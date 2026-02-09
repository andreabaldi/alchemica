<?php

/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Fotografia Alchemica';

$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
    <br> <br> <br>
    <div class="d-flex align-items-center mb-3">
        <img src="<?= Yii::$app->request->getBaseUrl(true); ?>/alchemica.jpg"
             style="width: 80px; height: 80px; object-fit: contain;" class="mr-3" />

        <div>
            <span style="font-size: 1.2rem; font-weight: 500;">
                Fotografia Alchemica LAB: designed for Alternative Printing
            </span>
            <br> <br> <br>
            <b class="text-muted">
                & for the people who are still using real chemicals and paper to print
            </b>
        </div>

        <img src="<?= Yii::$app->request->getBaseUrl(true); ?>/alchemica.jpg"
             style="width: 80px; height: 80px; object-fit: contain;" class="ml-3" />
    </div>
</div>