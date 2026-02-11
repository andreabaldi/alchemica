<?php
namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class LutController extends Controller {

    public function actionIndex() {
        $path = Yii::getAlias('@frontend/web/uploads/luts');
        if (!is_dir($path)) {
            FileHelper::createDirectory($path, 0775);
        }

        // Recupera i file esistenti
        $files = glob($path . '/*.{png,acv,cube}', GLOB_BRACE);

        return $this->render('index', [
            'files' => $files,
        ]);
    }

    public function actionUpload() {
        $file = UploadedFile::getInstanceByName('lutFile');
        if ($file) {
            $path = Yii::getAlias('@frontend/web/uploadS/lutS/') . $file->name;
            $file->saveAs($path);
            Yii::$app->session->setFlash('success', "LUT caricata correttamente.");
        }
        return $this->redirect(['index']);
    }

    public function actionDelete($name) {
        $path = Yii::getAlias('@frontend/web/uploadS/lutS/') . $name;
        if (file_exists($path)) {
            unlink($path);
            Yii::$app->session->setFlash('warning', "File rimosso.");
        }
        return $this->redirect(['index']);
    }
}