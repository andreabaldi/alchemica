<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\Presets;
use yii\web\Response;

class LinearizeController extends Controller
{
    public function actionIndex()
    {
        $presets = Presets::find()->all();
        return $this->render('index', [
            'presets' => $presets,
        ]);
    }

    public function actionGenerateFiles()
    {
        if (ob_get_level()) ob_end_clean();

        $request = Yii::$app->request;
        $ls = $request->post('l');
        $as = $request->post('a');
        $bs = $request->post('b');
        $lutName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->post('lut_name', 'SCTV_Export'));

        // Recuperiamo il JSON calcolato dalla vista
        $correctionsJson = $request->post('output_data');
        $corrections = json_decode($correctionsJson, true);

        if (!$ls || !$corrections) {
            return $this->redirect(['index']);
        }

        // --- CALCOLO SCTV PER IL REPORT ---
        $measured = [];
        $p0 = ['l' => (float)$ls[0], 'a' => (float)$as[0], 'b' => (float)$bs[0]];
        $p100 = ['l' => (float)$ls[100], 'a' => (float)$as[100], 'b' => (float)$bs[100]];
        $den = pow($p0['l'] - $p100['l'], 2) + pow($p0['a'] - $p100['a'], 2) + pow($p0['b'] - $p100['b'], 2);

        foreach ($ls as $pct => $lVal) {
            $num = pow($p0['l'] - (float)$lVal, 2) + pow($p0['a'] - (float)$as[$pct], 2) + pow($p0['b'] - (float)$bs[$pct], 2);
            $measured[(int)$pct] = ($den <= 0) ? (float)$pct : sqrt($num / $den) * 100;
        }

        // --- GENERAZIONE ZIP ---
        $zipPath = Yii::getAlias('@runtime/') . $lutName . "_" . time() . ".zip";
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {

            // --- FILE CSV COMPATIBILE E PULITO ---
            $csv = "Target_%,CIE L,CIE A,CIE B,SCTV_Measured,Correction_Output\r\n";

            foreach ($ls as $pct => $lVal) {
                $mVal = number_format($measured[$pct], 4, '.', '');
                $cVal = number_format($corrections[$pct], 4, '.', '');

                // Scriviamo le colonne nell'ordine dell'header
                $csv .= "$pct,$lVal,{$as[$pct]},{$bs[$pct]},$mVal,$cVal\r\n";
            }
            $zip->addFromString($lutName . "_Report_CIE.csv", $csv);

            // 2. ACV BINARIO (14 PUNTI - TESTATO)
            // Definiamo i 14 punti di campionamento approvati
            $samplePoints = [0, 5, 10, 20, 25, 30, 40, 50, 60, 70, 80, 90, 95, 100];

            // Header: Version (1), NumCurves (1)
            $acv = pack('n', 1) . pack('n', 1);
            // Curve Header: NumPoints (14)
            $acv .= pack('n', count($samplePoints));

            foreach ($samplePoints as $pct) {
                $outVal = (float)$corrections[$pct];
                // Photoshop mapping 0-100 -> 0-255
                $vOut = (int)round($outVal * 2.55);
                $vIn  = (int)round($pct * 2.55);

                // Scrittura: Output (Y) poi Input (X)
                $acv .= pack('n', $vOut);
                $acv .= pack('n', $vIn);
            }
            $zip->addFromString($lutName . ".acv", $acv);

            // 3. AMP (256 byte)
            $amp = "";
            for ($i = 0; $i <= 255; $i++) {
                $val = $this->interpolate(($i / 255) * 100, $corrections);
                $amp .= chr((int)round($val * 2.55));
            }
            $zip->addFromString($lutName . ".amp", $amp);

            // 4. CUBE (1D LUT)
            $cube = "TITLE \"$lutName\"\nLUT_1D_SIZE 256\nDOMAIN_MIN 0.0 0.0 0.0\nDOMAIN_MAX 1.0 1.0 1.0\n";
            for ($i = 0; $i <= 255; $i++) {
                $v = number_format($this->interpolate(($i/255)*100, $corrections) / 100, 6, '.', '');
                $cube .= "$v $v $v\n";
            }
            $zip->addFromString($lutName . ".cube", $cube);

            $zip->close();
        }

        return Yii::$app->response->sendFile($zipPath, $lutName . ".zip")
            ->on(\yii\web\Response::EVENT_AFTER_SEND, function($e) {
                if (file_exists($e->data)) unlink($e->data);
            }, $zipPath);
    }

// Funzione di supporto per generare i 256 punti AMP/CUBE partendo dai 21 della vista
    private function interpolate($x, $p)
    {
        $k = array_keys($p);
        sort($k, SORT_NUMERIC);
        for ($i = 0; $i < count($k) - 1; $i++) {
            if ($x >= $k[$i] && $x <= $k[$i + 1]) {
                return (float)$p[$k[$i]] + ($x - $k[$i]) * ((float)$p[$k[$i + 1]] - (float)$p[$k[$i]]) / ($k[$i + 1] - $k[$i]);
            }
        }
        return ($x <= $k[0]) ? $p[$k[0]] : end($p);
    }

}