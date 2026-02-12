<?php

namespace common\components;

use Yii;
use Imagick;
use ImagickDraw;
use ImagickPixel;

/**
 * AlchemichEngine - Versione 2026.02.08.DOCKER_READY
 * Fix definitivo font e layout professionale.
 */
class AlchemichEngine
{
    public $formats = [
        'A4' => ['w' => 210, 'h' => 297],
        'A3' => ['w' => 297, 'h' => 420],
        'Letter' => ['w' => 215.9, 'h' => 279.4],
        'Fixon' => ['w' => 316, 'h' => 256],
    ];

    /**
     * Ritorna il percorso di un font valido prioritizzando quello locale
     */
    private function getBestFont()
    {
        $fontPaths = [
            Yii::getAlias('@frontend/web/fonts/LiberationSans-Regular.ttf'), // PRIORITÀ 1: Il tuo file locale
            Yii::getAlias('@frontend/web/fonts/Arial.ttf'),                // PRIORITÀ 2: Fallback locale
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf', // PRIORITÀ 3: Docker Linux
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',             // PRIORITÀ 4: Docker Linux alt
            '/System/Library/Fonts/Supplemental/Arial.ttf',                 // Fallback Mac
        ];

        foreach ($fontPaths as $fp) {
            if (file_exists($fp)) {
                return $fp;
            }
        }
        return null;
    }

    public function analyzeInputImage($img)
    {
        $profiles = $img->getImageProfiles('icc', true);
        $gamma = 2.2;
        $profileName = "sRGB Presunto";

        if (isset($profiles['icc'])) {
            $icc = $profiles['icc'];
            if (stripos($icc, 'ProPhoto') !== false) {
                $gamma = 1.8;
                $profileName = "ProPhoto RGB";
            } elseif (stripos($icc, 'Linear') !== false) {
                $gamma = 1.0;
                $profileName = "Lineare";
            } elseif (stripos($icc, 'Adobe') !== false) {
                $gamma = 2.2;
                $profileName = "Adobe RGB";
            }
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

        $gammaValues = [];
        if ($preset->gamma_mode === 'list' && !empty($preset->gamma_custom_list)) {
            $gammaValues = is_array($preset->gamma_custom_list) ? $preset->gamma_custom_list : explode(',', $preset->gamma_custom_list);
        } else {
            $numSteps = (!empty($params['gridSize']) && (int)$params['gridSize'] > 0) ? (int)$params['gridSize'] : 6;
            for ($i = 0; $i < $numSteps; $i++) {
                $gammaValues[] = (float)$preset->gamma_base + ($i * (float)$preset->gamma_step);
            }
        }
        $totalCells = count($gammaValues);

        $layout = $this->calculateLayout(
            $params['paperFormat'],
            $params['orientation'] ?? 'auto',
            $totalCells,
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

        $showWedgeNumbers = isset($params['wedgeNumbers']) ? (bool)$params['wedgeNumbers'] : true;
        $showRegMarks = isset($params['addRegMarks']) ? (bool)$params['addRegMarks'] : true;

        foreach ($gammaValues as $i => $gamma) {
            $cell = clone $source;
            $wedgeH = $params['addStepWedge'] ? 100 : 0;
            $effectiveH = $layout['cellH'] - $wedgeH;

            $cell->resizeImage($layout['cellW'], $effectiveH, Imagick::FILTER_LANCZOS, 1);
            $actualCellH = $cell->getImageHeight();

            $isFirstOriginal = ($i === 0 && ($params['keepFirstOriginal'] ?? false));
            $targetGamma = $isFirstOriginal ? 1.0 : (float)$gamma;
            $labelText = $isFirstOriginal ? "ORIGINALE" : "G: " . number_format($targetGamma, 2);

            if (!empty($params['mirrorImage'])) $cell->flopImage();
            if (!empty($params['invert'])) $cell->negateImage(false);
            $cell->gammaImage($targetGamma);
            if ($lutFileName) $this->applyCubeLut($cell, $lutFileName);

            $c = $i % $layout['cols'];
            $r = (int)floor($i / $layout['cols']);

            $posX = $layout['marginX'] + ($c * ($layout['cellW'] + $layout['gapX']));
            $posY = $layout['marginTop'] + ($r * ($layout['cellH'] + $layout['gapY']));

            $canvas->compositeImage($cell, Imagick::COMPOSITE_OVER, $posX, $posY);

            if ($params['addStepWedge']) {
                $wedgeY = $posY + $actualCellH + 15;
                $mirrorValue = $params['mirrorImage'] ?? false;

                $wedge = $this->createStepWedge(
                    $layout['cellW'],
                    100,
                    (int)$params['steps'],
                    (bool)$params['invert'],
                    $showWedgeNumbers,
                    $mirrorValue
                );

                $wedge->gammaImage($targetGamma);
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

        foreach ($labelsToDraw as $l) {
            $this->addLabel($canvas, $l['text'], $l['x'], $l['y'], $params['invert'], 30);
        }

        if ($showRegMarks) {
            $this->addRegistrationMarks($canvas, $layout, $params['invert']);
        }

        $infoLine = "PROFILO: " . $preset->technique_name . " | MOD: " . ($preset->gamma_mode === 'list' ? 'LISTA' : 'STEP');
        $this->addLabel($canvas, $infoLine, $layout['marginX'], $layout['gridHTotal'] - 20, $params['invert'], 24);

        $outName = 'TARGET_' . time() . '.tif';
        $canvas->writeImage($targetPath . $outName);

        return ['tiff' => $outName, 'preview' => $outName, 'analysis' => $sourceAnalysis, 'complete' => true];
    }

    public function calculateLayout($format, $orient, $num, $sW, $sH)
    {
        $p = $this->formats[$format] ?? $this->formats['A4'];
        $best = null;
        $tests = ($orient === 'landscape') ? [['w' => $p['h'], 'h' => $p['w']]] : [['w' => $p['w'], 'h' => $p['h']]];
        if ($orient === 'auto') $tests = [['w' => $p['w'], 'h' => $p['h']], ['w' => $p['h'], 'h' => $p['w']]];
        foreach ($tests as $t) {
            for ($c = 1; $c <= min($num, 5); $c++) {
                $res = $this->fitGrid($t['w'], $t['h'], $num, $c, $sW, $sH);
                if (!$best || ($res['cellW'] * $res['cellH']) > ($best['cellW'] * $best['cellH'])) $best = $res;
            }
        }
        return $best;
    }

    private function fitGrid($pW, $pH, $num, $cols, $sW, $sH)
    {
        $dpi = 300;
        $canW = (int)round(($pW / 25.4) * $dpi);
        $canH = (int)round(($pH / 25.4) * $dpi);
        $rows = (int)ceil($num / $cols);
        $gapX = 80;
        $cellW = (int)(($canW - ($gapX * ($cols + 1))) / $cols);
        $cellH = (int)($cellW * ($sH / $sW));
        $extraContentY = 100 + 45 + 15;
        $gapY = $extraContentY + ($gapX / 2);
        $mTop = $gapX;
        $gridHTotal = $mTop + ($rows * $cellH) + (($rows - 1) * $gapY) + $extraContentY + 20;

        if ($gridHTotal > $canH) {
            $scale = ($canH - $mTop - ($extraContentY + 20) - (($rows - 1) * $gapY)) / ($rows * $cellH);
            $cellW = (int)($cellW * $scale);
            $cellH = (int)($cellH * $scale);
            $gridHTotal = $canH;
        }
        $marginX = (int)(($canW - ($cols * $cellW) - (($cols - 1) * $gapX)) / 2);
        return ['canvasW' => $canW, 'canvasH' => $canH, 'cellW' => $cellW, 'cellH' => $cellH, 'cols' => $cols, 'marginTop' => $mTop, 'marginX' => $marginX, 'gapX' => $gapX, 'gapY' => $gapY, 'gridHTotal' => (int)$gridHTotal];
    }

    private function addLabel(&$canvas, $text, $x, $y, $invert, $size = 32)
    {
        $draw = new ImagickDraw();
        $font = $this->getBestFont();
        if ($font) $draw->setFont($font);

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

    public function createStepWedge($width, $height, $steps, $invert = false, $showNumbers = true, $mirrorText = false)
    {
        $width = (int)round($width);
        $height = (int)round($height);
        $wedge = new \Imagick();
        $wedge->newImage($width, $height, 'white');

        $stepW = $width / $steps;
        $circleRadius = (int)round(min($stepW, $height) * 0.25);
        $doMirror = ($invert == true || $invert == 1 || $invert == '1');

        for ($i = 0; $i < $steps; $i++) {
            $drawIdx = $doMirror ? ($steps - 1 - $i) : $i;
            $grayValue = $drawIdx / ($steps - 1);
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

            if ($showNumbers) {
                $isStepLight = $grayValue > 0.5;
                $circleColor = $isStepLight ? 'black' : 'white';
                $textColor = $isStepLight ? 'white' : 'black';

                $centerX = (int)round($posX + ($stepW / 2));
                $centerY = (int)round($height / 2);

                $draw->clear();
                $draw->setFillColor($circleColor);
                $draw->circle($centerX, $centerY, $centerX + $circleRadius, $centerY);
                $wedge->drawImage($draw);

                $label = (string)($drawIdx + 1);
                $fontSize = (int)round($circleRadius * 1.3);

                $drawText = new \ImagickDraw();
                $font = $this->getBestFont();
                if ($font) $drawText->setFont($font);

                $drawText->setFontSize($fontSize);
                $drawText->setFillColor($textColor);
                $drawText->setTextAlignment(\Imagick::ALIGN_CENTER);

                $metrics = $wedge->queryFontMetrics($drawText, $label);
                $textY = $centerY + (($metrics['ascender'] + $metrics['descender']) / 2);

                $wedge->annotateImage($drawText, $centerX, $textY, 0, $label);
                $drawText->destroy();
            }
            $draw->destroy();
            $pixel->destroy();
        }

        if ($doMirror) $wedge->flopImage();
        return $wedge;
    }

    public function addRegistrationMarks(&$canvas, $layout, $invert)
    {
        $draw = new ImagickDraw();
        $size = 60;
        $padding = 20;
        $color = $invert ? 'white' : 'black';
        $draw->setStrokeColor($color);
        $draw->setFillColor('transparent');
        $draw->setStrokeWidth(1.2);
        $bottomY = $layout['gridHTotal'] - $size - 80;
        $points = [['x' => $padding, 'y' => $padding], ['x' => $layout['canvasW'] - $padding - $size, 'y' => $padding], ['x' => $padding, 'y' => $bottomY], ['x' => $layout['canvasW'] - $padding - $size, 'y' => $bottomY]];
        foreach ($points as $p) {
            $cx = $p['x'] + ($size / 2);
            $cy = $p['y'] + ($size / 2);
            $r = $size / 2;
            $draw->circle($cx, $cy, $cx + $r, $cy);
            $draw->line($cx - $r, $cy, $cx + $r, $cy);
            $draw->line($cx, $cy - $r, $cx, $cy + $r);
            $d = $r * 0.707;
            $draw->line($cx - $d, $cy - $d, $cx + $d, $cy + $d);
            $draw->line($cx + $d, $cy - $d, $cx - $d, $cy + $d);
        }
        $canvas->drawImage($draw);
        $draw->destroy();
    }

    public function applyCubeLut(&$image, $lutName)
    {
        $path = Yii::getAlias('@frontend/web/uploads/luts/') . $lutName;
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $pts = [];
        foreach ($lines as $l) {
            if (preg_match('/^[0-9]/', $l)) {
                $p = preg_split('/\s+/', trim($l));
                if (count($p) >= 3) $pts[] = $p;
            }
        }
        if (empty($pts)) return;
        $map = new Imagick();
        $map->newImage(count($pts), 1, new ImagickPixel('black'));
        $draw = new ImagickDraw();
        foreach ($pts as $i => $p) {
            $pixel = new ImagickPixel();
            $pixel->setColorValue(\Imagick::COLOR_RED, $p[0]);
            $pixel->setColorValue(\Imagick::COLOR_GREEN, $p[1]);
            $pixel->setColorValue(\Imagick::COLOR_BLUE, $p[2]);
            $draw->setFillColor($pixel);
            $draw->point($i, 0);
            $pixel->destroy();
        }
        $map->drawImage($draw);
        $image->clutImage($map);
        $map->destroy();
    }

    public function generateLutTestStrip($params)
    {
        $targetPath = Yii::getAlias('@frontend/web/uploads/targets/');

        $steps        = isset($params['steps']) ? (int)$params['steps'] : 21;
        $invert       = isset($params['invert']) ? (bool)$params['invert'] : false;
        $showNumbers  = isset($params['wedgeNumbers']) ? (bool)$params['wedgeNumbers'] : true;
        $applyLutFlag = isset($params['applyLut']) ? (bool)$params['applyLut'] : false;
        $lutFile      = isset($params['lutFile']) ? $params['lutFile'] : null;

        $side    = 600;
        $footerH = 250;
        $canvasW = $side;
        $canvasH = $side + $footerH;

        $canvas = new Imagick();
        $canvas->newImage($canvasW, $canvasH, 'black');
        $canvas->setImageFormat('tiff');
        $canvas->setImageDepth(16);

        $patch = new ImagickDraw();
        $color = $invert ? 'white' : 'rgb(128,128,128)';
        $patch->setFillColor(new ImagickPixel($color));
        $patch->rectangle(0, 0, $side, $side);
        $canvas->drawImage($patch);

        $wedgeW = $canvasW - 100;
        $wedgeH = 100;
        $wedgeX = 50;
        $wedgeY = $side + 60;

        $wedge = $this->createStepWedge($wedgeW, $wedgeH, $steps, $invert, $showNumbers, false);
        $canvas->compositeImage($wedge, Imagick::COMPOSITE_OVER, $wedgeX, $wedgeY);

        $draw = new ImagickDraw();
        $font = $this->getBestFont();
        if ($font) $draw->setFont($font);

        $draw->setFillColor('white');
        $draw->setTextAntialias(true);
        $draw->setFontSize(22);

        $labelStart = $invert ? "D-MIN (0%)" : "D-MAX (100%)";
        $canvas->annotateImage($draw, $wedgeX, $wedgeY - 15, 0, $labelStart);

        $labelEnd = $invert ? "D-MAX (100%)" : "D-MIN (0%)";
        $draw->setTextAlignment(\Imagick::ALIGN_RIGHT);
        $canvas->annotateImage($draw, $wedgeX + $wedgeW, $wedgeY - 15, 0, $labelEnd);

        if ($applyLutFlag && !empty($lutFile)) {
            $this->applyCubeLut($canvas, $lutFile);
            $statusLut = "CALIBRATED";
        } else {
            $statusLut = "LINEAR";
        }

        $draw->setTextAlignment(\Imagick::ALIGN_LEFT);
        $draw->setFontSize(18);
        $footerLabel = "STRIP UV | STEPS: {$steps} | {$statusLut} | " . ($invert ? "NEG" : "POS");
        $canvas->annotateImage($draw, 20, $canvasH - 30, 0, $footerLabel);

        $outName = 'UV_STRIP_FINAL_' . time() . '.tif';
        $canvas->writeImage($targetPath . $outName);

        $wedge->destroy();
        $draw->destroy();

        return ['tiff' => $outName, 'complete' => true];
    }
}