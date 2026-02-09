<?php

namespace app\models;

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
 * @property int|null $created_at
 * @property int|null $updated_at
 */
class Presets extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'presets';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['gamma_base', 'gamma_step', 'paper_name', 'uv_exposure_seconds', 'notes', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['color_hex'], 'default', 'value' => '#000000'],
            [['ink_limit'], 'default', 'value' => 100],
            [['technique_name'], 'required'],
            [['gamma_base', 'gamma_step'], 'number'],
            [['ink_limit', 'uv_exposure_seconds', 'created_at', 'updated_at'], 'integer'],
            [['notes'], 'string'],
            [['technique_name', 'paper_name'], 'string', 'max' => 255],
            [['color_hex'], 'string', 'max' => 7],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'technique_name' => 'Technique Name',
            'gamma_base' => 'Gamma Base',
            'gamma_step' => 'Gamma Step',
            'color_hex' => 'Color Hex',
            'ink_limit' => 'Ink Limit',
            'paper_name' => 'Paper Name',
            'uv_exposure_seconds' => 'Uv Exposure Seconds',
            'notes' => 'Notes',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
