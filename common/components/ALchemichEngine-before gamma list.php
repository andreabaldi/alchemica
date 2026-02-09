<?php

namespace common\components;

use Yii;
use Imagick;
use ImagickDraw;
use ImagickPixel;

/**
 * AlchemichEngine - Versione 2026.02.03.FIX_FONTS
 * Corretti i percorsi font e integrazione info profilo nel margine.
 */
class AlchemichEngine
{
    public $formats = [
        'A4' => ['w' => 210, 'h' => 297],
        'A3' => ['w' => 297, 'h' => 420],
        'Letter' => ['w' => 215.9, 'h' => 279.4],
    ];

    public function analyzeInputImage($img) {
        $profiles = $img->getImageProfiles('icc', true);
        $gamma = 2.2;
        $profileName = "sRGB Presunto";

        if (isset($profiles['icc'])) {
            $icc = $profiles['icc'];
            if (stripos($icc, 'ProPhoto') !== false) { $gamma = 1.8; $profileName = "ProPhoto RGB"; }
            elseif (stripos($icc, 'Linear') !== false) { $gamma = 1.0; $profileName = "Lineare"; }
            elseif (stripos($icc, 'Adobe') !== false) { $gamma = 2.2; $profileName = "Adobe RGB"; }
        }

        return [
            'gamma' => $gamma,
            'profile' => $profileName,
            'colorspace' => $img->getImageColorspace(),
            'depth' => $img->getImageDepth()
        ];
    }

    public function generateGrid($sourceFile, $preset, $params)
    {
        $targetPath = Yii::getAlias('@frontend/web/uploads/targets/');
        $source = new Imagick($targetPath . $sourceFile);
        $sourceAnalysis = $this->analyzeInputImage($source);
        $source->stripImage();
        $source->setImageColorspace(Imagick::COLORSPACE_SRGB);

        $layout = $this->calculateLayout(
            $params['paperFormat'],
            $params['orientation'] ?? 'auto',
            (int)$params['gridSize'],
            $source->getImageWidth(),
            $source->getImageHeight()
        );

        $canvas = new Imagick();
        $canvas->newImage($layout['canvasW'], $layout['canvasH'], 'white');
        $canvas->setImageFormat('tiff');
        $canvas->setImageDepth(16);

        if (!empty($params['invert'])) {
            $drawBg = new ImagickDraw();
            $drawBg->setFillColor('black');
            $drawBg->rectangle(0, 0, $layout['canvasW'], $layout['gridHTotal']);
            $canvas->drawImage($drawBg);
        }

        $lutFileName = ($params['applyLut'] && !empty($params['lutFile'])) ? $params['lutFile'] : null;
        $labelsToDraw = [];

        // Parametri opzionali dalla GUI
        $showWedgeNumbers = isset($params['wedgeNumbers']) ? (bool)$params['wedgeNumbers'] : true;
        $showRegMarks = isset($params['addRegMarks']) ? (bool)$params['addRegMarks'] : true;

        for ($i = 0; $i < (int)$params['gridSize']; $i++) {
            $cell = clone $source;
            $wedgeH = $params['addStepWedge'] ? 100 : 0;
            $effectiveH = $layout['cellH'] - $wedgeH;

            $cell->resizeImage($layout['cellW'], $effectiveH, Imagick::FILTER_LANCZOS, 1);
            $actualCellH = $cell->getImageHeight();

            $gamma = ($i === 0 && ($params['keepFirstOriginal'] ?? false)) ? 1.0 : $preset->gamma_base + ($i * $preset->gamma_step);
            $labelText = ($i === 0 && ($params['keepFirstOriginal'] ?? false)) ? "ORIGINALE" : "G: " . number_format($gamma, 2);

            if (!empty($params['mirrorImage'])) $cell->flopImage();
            if (!empty($params['invert'])) $cell->negateImage(false);
            $cell->gammaImage($gamma);
            if ($lutFileName) $this->applyCubeLut($cell, $lutFileName);

            $c = $i % $layout['cols'];
            $r = (int)floor($i / $layout['cols']);

            $posX = $layout['marginX'] + ($c * ($layout['cellW'] + $layout['gapX']));
            $posY = $layout['marginTop'] + ($r * ($layout['cellH'] + $layout['gapY']));

            $canvas->compositeImage($cell, Imagick::COMPOSITE_OVER, $posX, $posY);

            if ($params['addStepWedge']) {
                $wedgeY = $posY + $actualCellH + 15;
                // Chiamata alla nuova funzione con supporto per numeri opzionali
                // Modifica questa riga dentro generateGrid (intorno alla riga 104)
                $wedge = $this->createStepWedge(
                    $layout['cellW'],
                    100,
                    (int)$params['steps'],
                    (bool)$params['invert'],
                    $showWedgeNumbers,
                    (bool)($params['mirrorImage'] ?? false) // AGGIUNGI QUESTO PARAMETRO
                );

                $wedge->gammaImage($gamma);
                if ($lutFileName) $this->applyCubeLut($wedge, $lutFileName);
                $canvas->compositeImage($wedge, Imagick::COMPOSITE_OVER, $posX, $wedgeY);
                $textY = $wedgeY + 100 + 45;
                $wedge->destroy();
            } else {
                $textY = $posY + $actualCellH + 45;
            }

            $labelsToDraw[] = ['text' => $labelText, 'x' => $posX, 'y' => $textY];
            $cell->destroy();
        }

        // Disegno etichette gamma
        foreach ($labelsToDraw as $l) {
            $this->addLabel($canvas, $l['text'], $l['x'], $l['y'], $params['invert'], 30);
        }

        // Aggiunta Mire di Registro se attive
        if ($showRegMarks) {
            $this->addRegistrationMarks($canvas, $layout, $params['invert']);
        }

        // Info Profilo integrate nel fondo dell'area tecnica
        $infoLine = "PROFILO: " . $preset->technique_name . " | BASE: " . number_format($preset->gamma_base, 2);
        $this->addLabel($canvas, $infoLine, $layout['marginX'], $layout['gridHTotal'] - 20, $params['invert'], 24);

        $outName = 'TARGET_' . time() . '.tif';
        $canvas->writeImage($targetPath . $outName);

        return ['tiff' => $outName, 'preview' => $outName, 'analysis' => $sourceAnalysis, 'complete' => true];
    }

    private function fitGrid($pW, $pH, $num, $cols, $sW, $sH) {
        $dpi = 300;
        $canW = (int)round(($pW/25.4)*$dpi);
        $canH = (int)round(($pH/25.4)*$dpi);
        $rows = (int)ceil($num / $cols);

        $gapX = 80;
        $cellW = (int)(($canW - ($gapX * ($cols + 1))) / $cols);
        $cellH = (int)($cellW * ($sH / $sW));

        $extraContentY = 100 + 45 + 15;
        $gapY = $extraContentY + ($gapX / 2);

        $mTop = $gapX;
        // Altezza finisce subito dopo l'ultimo testo (+ piccolo margine di sicurezza di 20px)
        $gridHTotal = $mTop + ($rows * $cellH) + (($rows - 1) * $gapY) + $extraContentY + 20;

        if ($gridHTotal > $canH) {
            $scale = ($canH - $mTop - ($extraContentY + 20) - (($rows - 1) * $gapY)) / ($rows * $cellH);
            $cellW = (int)($cellW * $scale);
            $cellH = (int)($cellH * $scale);
            $gridHTotal = $canH;
        }

        $marginX = (int)(($canW - ($cols * $cellW) - (($cols - 1) * $gapX)) / 2);

        return [
            'canvasW' => $canW, 'canvasH' => $canH, 'cellW' => $cellW, 'cellH' => $cellH,
            'cols' => $cols, 'marginTop' => $mTop, 'marginX' => $marginX,
            'gapX' => $gapX, 'gapY' => $gapY, 'gridHTotal' => (int)$gridHTotal
        ];
    }

    private function addLabel(&$canvas, $text, $x, $y, $invert, $size = 32) {
        $draw = new ImagickDraw();
        // --- RIPRISTINO PERCORSI FONT COMPLETI ---
        $fontPaths = [
            Yii::getAlias('@common/web/fonts/Arial.ttf'),
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/Library/Fonts/Arial.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf'
        ];
        foreach ($fontPaths as $fp) { if (file_exists($fp)) { $draw->setFont($fp); break; } }

        $draw->setFontSize($size);
        $draw->setFillColor($invert ? 'white' : 'black');
        $draw->setTextAntialias(true);

        if ($invert) {
            $metrics = $canvas->queryFontMetrics($draw, $text);
            $tW = (int)$metrics['textWidth'] + 10;
            $tH = (int)$metrics['textHeight'] + 10;
            $tImg = new Imagick();
            $tImg->newImage($tW, $tH, new ImagickPixel('transparent'));
            $tImg->annotateImage($draw, 0, $tH - 5, 0, $text);
            $tImg->flopImage();
            $canvas->compositeImage($tImg, Imagick::COMPOSITE_OVER, $x, $y - $tH);
            $tImg->destroy();
        } else {
            $canvas->annotateImage($draw, $x, $y, 0, $text);
        }
        $draw->destroy();
    }

    private function calculateLayout($format, $orient, $num, $sW, $sH) {
        $p = $this->formats[$format] ?? $this->formats['A4'];
        $best = null;
        $tests = ($orient === 'landscape') ? [['w'=>$p['h'], 'h'=>$p['w']]] : [['w'=>$p['w'], 'h'=>$p['h']]];
        if ($orient === 'auto') $tests = [['w'=>$p['w'], 'h'=>$p['h']], ['w'=>$p['h'], 'h'=>$p['w']]];
        foreach ($tests as $t) {
            for ($c = 1; $c <= min($num, 5); $c++) {
                $res = $this->fitGrid($t['w'], $t['h'], $num, $c, $sW, $sH);
                if (!$best || ($res['cellW'] * $res['cellH']) > ($best['cellW'] * $best['cellH'])) $best = $res;
            }
        }
        return $best;
    }

    private function createsimpleStepWedge($width, $height, $steps, $invert = false) {
        $wedge = new Imagick(); $wedge->newImage($width, $height, 'white');
        $stepW = $width / $steps;
        for ($i = 0; $i < $steps; $i++) {
            $draw = new ImagickDraw(); $gray = $i / ($steps - 1);
            if ($invert) $gray = 1.0 - $gray;
            $pixel = new ImagickPixel(); $pixel->setColorValue(\Imagick::COLOR_RED, $gray);
            $pixel->setColorValue(\Imagick::COLOR_GREEN, $gray); $pixel->setColorValue(\Imagick::COLOR_BLUE, $gray);
            $draw->setFillColor($pixel); $draw->rectangle($i * $stepW, 0, ($i + 1) * $stepW, $height);
            $wedge->drawImage($draw); $draw->destroy(); $pixel->destroy();
        }
        return $wedge;
    }

    private function createStepWedge($width, $height, $steps, $invert = false, $showNumbers = true, $mirrorText = false) {
        $width = (int)round($width);
        $height = (int)round($height);

        $wedge = new \Imagick();
        $wedge->newImage($width, $height, 'white');

        $stepW = $width / $steps;
        $circleRadius = (int)round(min($stepW, $height) * 0.25);

        $fontPaths = [
            \Yii::getAlias('@common/web/fonts/Arial.ttf'),
            '/System/Library/Fonts/Supplemental/Arial.ttf',
            '/Library/Fonts/Arial.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf'
        ];

        for ($i = 0; $i < $steps; $i++) {
            $grayValue = $i / ($steps - 1);
            if ($invert) $grayValue = 1.0 - $grayValue;

            $posX = (int)round($i * $stepW);
            $nextX = (int)round(($i + 1) * $stepW);

            $draw = new \ImagickDraw();
            $pixel = new \ImagickPixel();
            $pixel->setColorValue(\Imagick::COLOR_RED, (float)$grayValue);
            $pixel->setColorValue(\Imagick::COLOR_GREEN, (float)$grayValue);
            $pixel->setColorValue(\Imagick::COLOR_BLUE, (float)$grayValue);

            $draw->setFillColor($pixel);
            $draw->rectangle($posX, 0, $nextX, $height);
            $wedge->drawImage($draw);
            $draw->clear();

            if ($showNumbers) {
                $isStepLight = $grayValue > 0.5;
                $circleColor = $isStepLight ? 'black' : 'white';
                $textColor = $isStepLight ? 'white' : 'black';

                $centerX = (int)round($posX + ($stepW / 2));
                $centerY = (int)round($height / 2);

                // 1. Disegno il bollino
                $draw->setFillColor($circleColor);
                $draw->circle($centerX, $centerY, $centerX + $circleRadius, $centerY);
                $wedge->drawImage($draw);
                $draw->clear();

                // 2. Logica Numero
                $label = (string)($i + 1);
                $fontSize = (int)round($circleRadius * 1.3);

                $drawText = new \ImagickDraw();
                foreach ($fontPaths as $fp) { if (file_exists($fp)) { $drawText->setFont($fp); break; } }
                $drawText->setFontSize($fontSize);
                $drawText->setFillColor($textColor);
                $drawText->setTextAlignment(\Imagick::ALIGN_CENTER);
                $drawText->setTextAntialias(true);

                $metrics = $wedge->queryFontMetrics($drawText, $label);

                // Area quadrata generosa per evitare clipping (2.5x raggio)
                $tDim = (int)round($circleRadius * 2.5);
                $tImg = new \Imagick();
                $tImg->newImage($tDim, $tDim, 'transparent');

                // Centratura calcolata sulle metriche del font
                $textX = $tDim / 2;
                $textY = ($tDim / 2) + (($metrics['ascender'] + $metrics['descender']) / 2);

                $tImg->annotateImage($drawText, $textX, $textY, 0, $label);

                // --- IL FIX DELLA LOGICA ---
                // Se mirrorText è "1" (stringa) o true (bool), ribaltiamo.
                if ($mirrorText == true || $mirrorText === 1 || $mirrorText === '1') {
                    $tImg->flopImage();
                }

                $wedge->compositeImage($tImg, \Imagick::COMPOSITE_OVER, (int)($centerX - ($tDim / 2)), (int)($centerY - ($tDim / 2)));

                $tImg->destroy();
                $drawText->destroy();
            }
            $draw->destroy();
            $pixel->destroy();
        }

        return $wedge;
    }


    private function addRegistrationMarks(&$canvas, $layout, $invert) {
        $draw = new ImagickDraw();
        $size = 60;      // Dimensione richiesta: 60px
        $padding = 20;
        $color = $invert ? 'white' : 'black';

        $draw->setStrokeColor($color);
        $draw->setFillColor('transparent');
        $draw->setStrokeWidth(1.2);

        // Calcoliamo la coordinata Y inferiore per farle stare sopra le info profilo
        // gridHTotal è la fine del nero; le info profilo sono a -25.
        // Mettiamo le mire di fondo a circa -100 dal fondo del nero.
        $bottomY = $layout['gridHTotal'] - $size - 80;

        $points = [
            ['x' => $padding, 'y' => $padding], // Top Left
            ['x' => $layout['canvasW'] - $padding - $size, 'y' => $padding], // Top Right
            ['x' => $padding, 'y' => $bottomY], // Bottom Left (sopra info profilo)
            ['x' => $layout['canvasW'] - $padding - $size, 'y' => $bottomY] // Bottom Right
        ];

        foreach ($points as $p) {
            $cx = $p['x'] + ($size / 2);
            $cy = $p['y'] + ($size / 2);
            $r = $size / 2;

            // Cerchio
            $draw->circle($cx, $cy, $cx + $r, $cy);

            // Croce e diagonali TUTTE INTERNE (niente raggi esterni che ingombrano)
            $draw->line($cx - $r, $cy, $cx + $r, $cy);
            $draw->line($cx, $cy - $r, $cx, $cy + $r);

            $d = $r * 0.707;
            $draw->line($cx - $d, $cy - $d, $cx + $d, $cy + $d);
            $draw->line($cx + $d, $cy - $d, $cx - $d, $cy + $d);
        }

        $canvas->drawImage($draw);
        $draw->destroy();
    }

    private function applyCubeLut(&$image, $lutName) {
        $path = Yii::getAlias('@frontend/web/uploads/luts/') . $lutName;
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); $pts = [];
        foreach ($lines as $l) { if (preg_match('/^[0-9]/', $l)) { $p = preg_split('/\s+/', trim($l)); if (count($p) >= 3) $pts[] = $p; } }
        if (empty($pts)) return;
        $map = new Imagick(); $map->newImage(count($pts), 1, new ImagickPixel('black'));
        $draw = new ImagickDraw();
        foreach ($pts as $i => $p) {
            $pixel = new ImagickPixel(); $pixel->setColorValue(\Imagick::COLOR_RED, $p[0]);
            $pixel->setColorValue(\Imagick::COLOR_GREEN, $p[1]); $pixel->setColorValue(\Imagick::COLOR_BLUE, $p[2]);
            $draw->setFillColor($pixel); $draw->point($i, 0); $pixel->destroy();
        }
        $map->drawImage($draw); $image->clutImage($map); $map->destroy();
    }
}