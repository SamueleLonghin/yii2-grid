<?php


namespace samuelelonghin\grid;


use kartik\mpdf\Pdf;

class Module extends \kartik\grid\Module
{
    public $pdfConfig = ['orientation' => Pdf::ORIENT_PORTRAIT];
}