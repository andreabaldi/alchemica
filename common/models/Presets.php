<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "presets".
 *
 * @property int $id
 * @property string $technique_name
 * @property float|null $gamma_base
 * @property float|null $gamma_step
 * @property string|null $color_hex
 * @property int|null $ink_limit
 * @property string|null $paper_name
 * @property int|null $uv_exposure_seconds
 * @property string|null $notes
 * @property string|null $gamma_mode
 * @property string|null $gamma_custom_list
 * @property bool|null $show_wedge_default
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Presets extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'presets';
    }

    public function rules()
    {
        return [
            [['gamma_base', 'gamma_step', 'paper_name', 'uv_exposure_seconds', 'notes', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['color_hex'], 'default', 'value' => '#000000'],
            [['ink_limit'], 'default', 'value' => 100],
            [['gamma_mode'], 'default', 'value' => 'step'],
            [['technique_name'], 'required'],
            [['gamma_base', 'gamma_step'], 'number'],
            [['ink_limit', 'uv_exposure_seconds', 'created_at', 'updated_at'], 'integer'],
            [['notes'], 'string'],
            [['technique_name', 'paper_name'], 'string', 'max' => 255],
            [['gamma_mode'], 'string', 'max' => 20],
            [['gamma_custom_list'], 'safe'],
            [['show_wedge_default'], 'boolean'],
            [['color_hex'], 'string', 'max' => 7],
            ['gamma_custom_list', 'validateGammaList'],
        ];
    }

    public function validateGammaList($attribute, $params)
    {
        $values = $this->$attribute;
        if (empty($values)) return;
        $list = is_array($values) ? $values : explode(',', $values);
        foreach ($list as $val) {
            $val = trim($val);
            if (!is_numeric($val) || $val < 0.2 || $val > 5.0) {
                $this->addError($attribute, "Il valore Gamma $val non è valido (range 0.2 - 5.0).");
            }
        }
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'technique_name' => 'Nome Tecnica',
            'gamma_base' => 'Gamma Base',
            'gamma_step' => 'Gamma Step',
            'gamma_mode' => 'Modalità Gamma',
            'gamma_custom_list' => 'Lista Gamma Custom',
            'paper_name' => 'Carta',
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Se l'attributo è un array (che provenga dal form o da afterFind),
            // dobbiamo trasformarlo in stringa per il DB
            if (is_array($this->gamma_custom_list)) {
                // Puliamo i valori vuoti e rimuoviamo duplicati
                $cleanList = array_unique(array_filter($this->gamma_custom_list, function($val) {
                    return $val !== null && $val !== '';
                }));

                // Ordiniamo per pulizia visiva
                sort($cleanList);

                // Trasformiamo in stringa separata da virgole
                $this->gamma_custom_list = implode(',', $cleanList);
            }

            // Se è vuoto, assicuriamoci che sia null e non un array vuoto
            if (empty($this->gamma_custom_list)) {
                $this->gamma_custom_list = null;
            }

            return true;
        }
        return false;
    }

    public function afterFind()
    {
        parent::afterFind();
        if (!empty($this->gamma_custom_list) && !is_array($this->gamma_custom_list)) {
            $this->gamma_custom_list = explode(',', $this->gamma_custom_list);
        }
    }
}