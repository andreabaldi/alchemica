<?php
namespace frontend\models;
use yii\base\Model;

class ExposureCalculator extends Model {
    public $baseUnits = 600;
    public $baseMinutes = 6;
    public $baseSeconds = 0;
    public $inputType = 'units';
    public $wedgeType = 31;
    public $dmaxStep = 1;
    public $dminStep = 31;

    public function rules() {
        return [
            [['inputType', 'dmaxStep', 'dminStep', 'wedgeType'], 'required'],
            [['baseUnits', 'baseMinutes', 'baseSeconds'], 'safe'],
            [['dmaxStep', 'dminStep', 'wedgeType', 'baseUnits', 'baseMinutes'], 'integer'],
        ];
    }

    public function calculate() {
        // Logica precisa: 31 step = 1/3 stop | 21 step = 1/2 stop
        $stepsPerStop = ($this->wedgeType == 31) ? 3 : 2;

        $totalInput = ($this->inputType === 'time')
            ? ((int)$this->baseMinutes * 60) + (float)$this->baseSeconds
            : (float)$this->baseUnits;

        $diffSteps = (int)$this->dmaxStep - 1;
        $factor = pow(2, ($diffSteps / $stepsPerStop));
        $finalValue = ($totalInput > 0) ? ($totalInput / $factor) : 0;

        return [
            'success' => true,
            'formatted' => $this->formatOutput($finalValue),
            'stops' => round($diffSteps / $stepsPerStop, 2),
            'diffSteps' => $diffSteps,
            'fractionLabel' => ($this->wedgeType == 31) ? "1/3 stop" : "1/2 stop",
            'is_complete' => true
        ];
    }

    private function formatOutput($val) {
        if ($this->inputType === 'time') {
            $val = round($val); // Arrotondiamo al secondo intero
            $m = floor($val / 60);
            $s = $val % 60;
            // Formato pulito mm:ss senza decimali
            return sprintf("%02d:%02d", $m, $s);
        }
        return round($val, 0);
    }
}