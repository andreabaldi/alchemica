<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * TargetUploadForm - Gestione parametri per Alchemich Engine AB
 */
class TargetUploadForm extends Model
{
    public $imageFile;
    public $presetId;
    public $gridSize = 1;
    public $paperFormat = 'A4';
    public $orientation = 'auto';
    public $invert = false;
    public $applyLut = false;
    public $lutFile;
    public $mirrorImage = false;
    public $keepFirstOriginal = false;
    public $addStepWedge = false;
    public $steps = 21;
    public $wedgeNumbers = true;
    public $addRegMarks = true;

    public function rules()
    {
        return [
            [['presetId', 'gridSize', 'paperFormat', 'orientation'], 'required'],
            [['paperFormat', 'orientation', 'lutFile'], 'string'],
            [['gridSize', 'steps'], 'integer', 'min' => 1, 'max' => 50],
            [['invert', 'applyLut', 'keepFirstOriginal', 'addStepWedge', 'mirrorImage', 'wedgeNumbers', 'addRegMarks'], 'boolean'],
            [['orientation'], 'default', 'value' => 'auto'],
            [['paperFormat'], 'default', 'value' => 'A4'],
            [['steps'], 'default', 'value' => 21],
            [['wedgeNumbers', 'addRegMarks'], 'default', 'value' => true],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, tiff, tif'],
        ];
    }

    /**
     * Normalizza i parametri garantendo tipi booleani puri per l'Engine.
     * Risolve il problema del mirroring non rilevato.
     */
    public function getProcessingParams()
    {
        return [
            'presetId'          => (int)$this->presetId,
            'gridSize'          => (int)$this->gridSize,
            'paperFormat'       => $this->paperFormat,
            'orientation'       => $this->orientation,
            'invert'            => (bool)$this->invert,
            'applyLut'          => (bool)$this->applyLut,
            'lutFile'           => $this->lutFile,
            'mirrorImage'       => (bool)$this->mirrorImage,
            'keepFirstOriginal' => (bool)$this->keepFirstOriginal,
            'addStepWedge'      => (bool)$this->addStepWedge,
            'steps'             => (int)$this->steps,
            'wedgeNumbers'      => (bool)$this->wedgeNumbers,
            'addRegMarks'       => (bool)$this->addRegMarks,
        ];
    }

    public function attributeLabels()
    {
        return [
            'presetId' => 'Tecnica (Preset)',
            'gridSize' => 'Numero di Celle',
            'paperFormat' => 'Formato Carta',
            'orientation' => 'Orientamento Foglio',
            'invert' => 'Inverti Immagine (Negativo)',
            'applyLut' => 'Applica Calibrazione LUT',
            'lutFile' => 'File LUT (.cube)',
            'mirrorImage' => 'Specchia Orizzontalmente',
            'keepFirstOriginal' => 'Prima cella senza Gamma',
            'addStepWedge' => 'Includi Scala di Grigi',
            'steps' => 'Livelli Scala',
            'wedgeNumbers' => 'Mostra Numeri Stepwedge',
            'addRegMarks' => 'Mire di Registro',
        ];
    }

    /**
     * Gestione fisica del caricamento file
     */
    public function upload()
    {
        if ($this->validate()) {
            $path = Yii::getAlias('@frontend/web/uploads/targets/');
            if (!is_dir($path)) {
                mkdir($path, 0777, true);
            }

            // Se non c'è un nuovo file caricato, usiamo quello in sessione o ritorniamo true
            if (!$this->imageFile) {
                return true;
            }

            $fileName = 'SRC_' . time() . '_' . $this->imageFile->baseName . '.' . $this->imageFile->extension;
            $fullPath = $path . $fileName;

            if ($this->imageFile->saveAs($fullPath)) {
                try {
                    $img = new \Imagick($fullPath);
                    $d = $img->getImageGeometry();
                    $maxSize = 3500;

                    // Ottimizzazione per non saturare la RAM di MAMP
                    if ($d['width'] > $maxSize || $d['height'] > $maxSize) {
                        $img->resizeImage($maxSize, $maxSize, \Imagick::FILTER_LANCZOS, 1, true);
                        $img->stripImage();
                        $img->writeImage($fullPath);
                    }
                    $img->destroy();
                } catch (\Exception $e) {
                    Yii::error("Errore elaborazione Imagick: " . $e->getMessage());
                }
                return $fileName;
            }
        }
        return false;
    }
}