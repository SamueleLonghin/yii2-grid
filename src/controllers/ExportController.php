<?php

namespace samuelelonghin\grid\controllers;

use kartik\mpdf\Pdf;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class ExportController extends \kartik\grid\controllers\ExportController
{
    public $defaultAction = 'download';

    protected function generatePDF($content, $filename, $config = [])
    {
        $config = ArrayHelper::merge($this->module->pdfConfig, $config);

        unset($config['contentBefore'], $config['contentAfter']);
        $config['filename'] = $filename;
        $config['methods']['SetAuthor'] = [ArrayHelper::getValue($config, 'author', \Yii::t('kvgrid', 'Samuele Longhin'))];
        $config['methods']['SetCreator'] = [ArrayHelper::getValue($config, 'creator', \Yii::t('kvgrid', 'Krajee Yii2 Grid Export Extension'))];
        $config['content'] = $content;
        $pdf = new Pdf($config);
        return $pdf->render();
    }

}