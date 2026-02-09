<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use common\models\Presets;
use frontend\models\TargetUploadForm;
use common\components\AlchemichEngine;

class LabControllerbeforegamma extends Controller
{
//    public function actionIndex()
//    {
//        $model = new TargetUploadForm();
//        $session = Yii::$app->session;
//
//        $presets = Presets::find()->all();
//        $resultFile = null;
//        $previewFile = null;
//
//        // Recupero parametri dalla sessione se presenti
//        if (!Yii::$app->request->isPost && $session->has('target_params')) {
//            $model->attributes = $session->get('target_params');
//        }
//
//        // Gestione lista LUT
//        $lutFolder = Yii::getAlias('@frontend/web/uploads/luts/');
//        $files = is_dir($lutFolder) ? array_diff(scandir($lutFolder), ['.', '..', '.DS_Store']) : [];
//        $lutList = !empty($files) ? array_combine($files, $files) : [];
//
//        if ($model->load(Yii::$app->request->post())) {
//
//            // Salvataggio in sessione
//            $session->set('target_params', $model->attributes);
//
//            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
//
//            if ($model->imageFile && ($fileName = $model->upload())) {
//                $engine = new AlchemichEngine();
//                $preset = Presets::findOne($model->presetId);
//
//                // Se il preset è nullo, fermiamo tutto prima di chiamare l'Engine
//                if (!$preset) {
//                    Yii::$app->session->setFlash('error', "Errore: Devi selezionare un Preset (Tecnica) valido.");
//                    return $this->render('index', [
//                        'model' => $model, 'presets' => $presets, 'lutList' => $lutList,
//                        'resultFile' => null, 'previewFile' => null,
//                    ]);
//                }
//
//                // Costruiamo i parametri assicurando che non ci siano valori NULL
//                // Usiamo l'operatore ?? per dare valori di default sicuri
//                $params = [
//                    'paperFormat'       => $model->paperFormat,
//                    'orientation'       => $model->orientation ?? 'auto',
//                    'gridSize'          => (int)($model->gridSize ?? 1),
//                    'invert'            => (bool)($model->invert ?? false),
//                    'applyLut'          => (bool)($model->applyLut ?? false),
//                    'lutFile'           => $model->lutFile,
//                    'keepFirstOriginal' => (bool)($model->keepFirstOriginal ?? false),
//                    'addStepWedge'      => (bool)($model->addStepWedge ?? false),
//                    'steps'             => (int)($model->steps ?? 21),
//                    'mirrorImage'       => (bool)($model->mirrorImage ?? false),
//                ];
//
//                try {
//                    $result = $engine->generateGrid($fileName, $preset, $params);
//
//                    // Verifichiamo che l'engine abbia risposto correttamente
//                    if ($result && isset($result['tiff'])) {
//                        $resultFile = $result['tiff'];
//                        $previewFile = $result['preview'];
//                        Yii::$app->session->setFlash('success', "Matrice generata correttamente.");
//                    }
//                } catch (\Exception $e) {
//                    Yii::$app->session->setFlash('error', "Errore Engine: " . $e->getMessage());
//                }
//            }
//        }
//
//        return $this->render('index', [
//            'model'       => $model,
//            'presets'     => $presets,
//            'lutList'     => $lutList,
//            'resultFile'  => $resultFile,
//            'previewFile' => $previewFile,
//        ]);
//    }


    public function actionIndex()
    {
        $model = new TargetUploadForm();
        $session = Yii::$app->session;

        $presets = Presets::find()->all();
        $resultFile = null;
        $previewFile = null;
        $analysis = null;
        $currentPreset = null;

        // 1. Recupero parametri dalla sessione per i campi testo/select
        if (!Yii::$app->request->isPost && $session->has('target_params')) {
            $model->attributes = $session->get('target_params');
        }

        // 2. Recuperiamo il nome dell'ultimo file usato dalla sessione
        $lastFile = $session->get('last_uploaded_file');

        // 3. Analisi per la UI (sempre attiva se abbiamo un file in memoria)
        if ($lastFile) {
            $filePath = Yii::getAlias('@frontend/web/uploads/targets/') . $lastFile;
            if (file_exists($filePath)) {
                $engine = new AlchemichEngine();
                $img = new \Imagick($filePath);
                $analysis = $engine->analyzeInputImage($img);
                $img->destroy();
            }
        }

        if ($model->presetId) {
            $currentPreset = \common\models\Presets::findOne($model->presetId);
        }

        $lutFolder = Yii::getAlias('@frontend/web/uploads/luts/');
        $files = is_dir($lutFolder) ? array_diff(scandir($lutFolder), ['.', '..', '.DS_Store']) : [];
        $lutList = !empty($files) ? array_combine($files, $files) : [];

        if ($model->load(Yii::$app->request->post())) {
            $session->set('target_params', $model->attributes);

            // Proviamo a prendere il nuovo file caricato
            $model->imageFile = \yii\web\UploadedFile::getInstance($model, 'imageFile');

            // --- QUESTA È LA PARTE CHE RISOLVE IL TUO PROBLEMA ---
            $fileName = null;
            if ($model->imageFile) {
                // Se l'utente ha caricato un NUOVO file, usiamo quello
                $fileName = $model->upload();
                $session->set('last_uploaded_file', $fileName);
            } else {
                // Se il campo è VUOTO, usiamo il file che era già in sessione
                $fileName = $lastFile;
            }
            // -----------------------------------------------------

            // Se abbiamo un file (nuovo o recuperato dalla sessione)
            if ($fileName) {
                $engine = new AlchemichEngine();
                $preset = \common\models\Presets::findOne($model->presetId);

                if ($preset) {
                    $params = [
                        'paperFormat'       => $model->paperFormat,
                        'orientation'       => $model->orientation ?? 'auto',
                        'gridSize'          => (int)($model->gridSize ?? 1),
                        'invert'            => (bool)($model->invert ?? false),
                        'applyLut'          => (bool)($model->applyLut ?? false),
                        'lutFile'           => $model->lutFile,
                        'keepFirstOriginal' => (bool)($model->keepFirstOriginal ?? false),
                        'addStepWedge'      => (bool)($model->addStepWedge ?? false),
                        'steps'             => (int)($model->steps ?? 21),
                        'mirrorImage'       => (bool)($model->mirrorImage ?? false),
                        // --- AGGIUNGI QUESTE DUE RIGHE ---
                        'wedgeNumbers' => $model->wedgeNumbers,
                        'addRegMarks'  => $model->addRegMarks,
                        // ---------------------------------
                    ];

                    try {
                        $result = $engine->generateGrid($fileName, $preset, $params);
                        if ($result && isset($result['tiff'])) {
                            $resultFile = $result['tiff'];
                            $previewFile = $result['preview'];
                            $analysis = $result['analysis'] ?? $analysis;
                            Yii::$app->session->setFlash('success', "Elaborazione completata.");
                        }
                    } catch (\Exception $e) {
                        Yii::$app->session->setFlash('error', "Errore: " . $e->getMessage());
                    }
                } else {
                    Yii::$app->session->setFlash('error', "Seleziona una tecnica.");
                }
            } else {
                Yii::$app->session->setFlash('error', "Nessun file caricato. Seleziona un'immagine.");
            }
        }

        return $this->render('index', [
            'model'       => $model,
            'presets'     => $presets,
            'lutList'     => $lutList,
            'resultFile'  => $resultFile,
            'previewFile' => $previewFile,
            'analysis'    => $analysis,
            'currentPreset' => $currentPreset,
        ]);
    }

    public function actionGenerateLutTest()
    {
        $model = new TargetUploadForm();
        if ($model->load(Yii::$app->request->post())) {
            $engine = new AlchemichEngine();

            $params = [
                'applyLut'    => (bool)($model->applyLut ?? false),
                'lutFile'     => $model->lutFile,
                'invert'      => (bool)($model->invert ?? false),
                'steps'       => (int)($model->steps ?? 31),
                'mirrorImage' => (bool)($model->mirrorImage ?? false),
            ];

            try {
                $fileName = $engine->generateLutTestStrip($params);
                if ($fileName) {
                    $path = Yii::getAlias('@frontend/web/uploads/targets/') . $fileName;
                    return Yii::$app->response->sendFile($path, "STRIP_UV_5x5.tif");
                }
            } catch (\Exception $e) {
                Yii::$app->session->setFlash('error', "Errore Strip: " . $e->getMessage());
            }
        }
        return $this->redirect(['index']);
    }

    /**
     * Download Sicuro dei file generati
     */
    public function actionDownload($name)
    {
        // Usiamo l'alias per trovare il percorso fisico sul disco
        $path = Yii::getAlias('@frontend/web/uploads/targets/') . $name;

        // Debug: Se il file non esiste, Yii darà errore 404.
        // Verifichiamo che il file sia effettivamente leggibile.
        if (file_exists($path)) {
            return Yii::$app->response->sendFile($path, $name, [
                'inline' => false,
                'mimeType' => 'image/tiff',
            ]);
        }

        // Se arrivi qui, il file non è fisicamente nella cartella uploads/targets/
        throw new \yii\web\NotFoundHttpException("Il file '{$name}' non è stato trovato sul server. Controlla la cartella uploads/targets/.");
    }
    public function actionFileManager()
    {
        $path = Yii::getAlias('@frontend/web/uploads/targets/');

        // --- AGGIUNTA: Logica di eliminazione ---
        if (Yii::$app->request->isPost) {
            $toDelete = Yii::$app->request->post('selection', []);
            foreach ($toDelete as $fileName) {
                $file = $path . $fileName;
                if (file_exists($file)) {
                    @unlink($file);
                }
            }
            return $this->refresh();
        }
        // --- FINE AGGIUNTA ---

        $files = [];
        if (is_dir($path)) {
            $found = array_diff(scandir($path), ['.', '..', '.DS_Store']);
            foreach ($found as $f) {
                // Aggiunto controllo is_file per evitare errori su eventuali sottocartelle
                if (is_file($path . $f)) {
                    $files[] = [
                        'name' => $f,
                        'size' => round(filesize($path . $f) / 1024 / 1024, 2) . ' MB',
                        'date' => date("d/m/Y H:i", filemtime($path . $f))
                    ];
                }
            }
        }
        return $this->render('file-manager', ['files' => $files]);
    }



    public function actionClearFile()
    {
        $session = Yii::$app->session;
        $session->remove('last_uploaded_file');
        $session->remove('target_params');
        return $this->redirect(['index']);
    }
}