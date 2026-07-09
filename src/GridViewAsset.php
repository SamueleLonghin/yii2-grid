<?php

/**
 * @package yii2-grid
 * @version 1.4.3
 */

namespace samuelelonghin\grid;

use kartik\base\WidgetAsset;
use kartik\bs5dropdown\DropdownAsset;
use yii\bootstrap5\BootstrapAsset;
use yii\bootstrap5\BootstrapPluginAsset;
use yii\web\AssetBundle;

class GridViewAsset extends AssetBundle
{
	public $sourcePath = __DIR__ . '/assets';
	public $depends = [
		BootstrapAsset::class,
		BootstrapPluginAsset::class,
		DropdownAsset::class,
		WidgetAsset::class
	];
//	public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
	public $css = [
		'css/sl-grid.css',
	];
	public $js = [
		'js/sl-grid.js',
	];
}
