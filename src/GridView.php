<?php

namespace samuelelonghin\grid;

use Exception;
use kartik\base\Config;
use kartik\export\ExportMenu;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use samuelelonghin\btn\Btn;
use samuelelonghin\db\ActiveQuery;
use Yii;
use yii\base\InvalidConfigException;
use yii\bootstrap5\Html;
use yii\data\ActiveDataProvider;
use yii\data\DataProviderInterface;
use yii\db\ActiveQueryTrait;
use yii\grid\Column;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;


/**
 * Class GridView
 * @package samuelelonghin\gridview
 *
 * @property Column[] $mergeColumns
 */
class GridView extends \kartik\grid\GridView
{
	public $collapse = false;
	public $collapsable = false;
	public $isAssociative = false;
	public $itemClass = false;
	/**
	 * @var bool|Column[]
	 */
	public $mergeColumns = false;
	/**
	 * @var ActiveQueryTrait |ActiveQuery
	 */
	public $query;
	/**
	 * @var DataProviderInterface|ActiveDataProvider
	 */
	public $exportProvider;
	public $rowClickUrl = false;
	public $rowClick = true;
	public $rowClickParams = null;
	public string $pk = 'id';
	public $baseColumns = [];
	public string $preGrid = '';
	public string $postGrid = '';
	/**
	 * @var bool|string
	 */
	public $title = false;
	public string $containerClass = 'rounded shadow mt-5 mb-5 p-3';
	public bool $visible = true;
	public $hover = true;
	public $striped = false;
	public $bordered = false;
	//	public $summary = '';
	public $showOnEmpty = false;
	public $responsive = true;
	public $responsiveWrap = false;
	public $emptyText = '';
	public $level = 0;
	public $cornerButton;
	public $cornerIcon;
	public $cornerButtonUrl;
	public $limit = null;
	public $attribute = null;

	private $isEmpty = false;

	public $moduleId = 'samuele-longhin-gridview';

	public $showExport = false;
	public $toggleData = false;
	public $exportColumns = [];
	public $exportMergeColumns = [];

	public $panelTemplate = <<<HTML
{panelBefore}
{items}
{panelAfter}
{panelFooter}
HTML;

	public $defaultExportStyle = [
		'borders' => [
			'outline' => [
				'borderStyle' => Border::BORDER_MEDIUM,
				'color' => ['argb' => Color::COLOR_BLACK],
			],
			'inside' => [
				'borderStyle' => Border::BORDER_DOTTED,
				'color' => ['argb' => Color::COLOR_BLACK],
			]
		],
		'font' => ['bold' => false, 'size' => 14],

	];


	/**
	 * @throws InvalidConfigException
	 */
	public function init(): void
	{
		if (!$this->visible)
			return;
		if (!isset($this->dataProvider)) {
			if (isset($this->query)) {
				$pagination = [];
				if (!is_null($this->limit)) {
					if ($this->limit)
						$pagination['pageSize'] = $this->limit;
				}
				$this->dataProvider = new ActiveDataProvider(['query' => $this->query, 'pagination' => $pagination]);
			} else {
				throw new InvalidConfigException('Il campo "query" deve essere impostato');
			}
		}
		if (!isset($this->exportProvider)) {
			$this->exportProvider = clone $this->dataProvider;
			if ($this->exportProvider->hasMethod('setPagination')) {
				$this->exportProvider->setPagination(false);
			}
		}
		if (!$this->dataProvider->count) {
			$this->isEmpty = true;
		}
		if (!$this->itemClass) {
			if (isset($this->dataProvider->query) && isset($this->dataProvider->query->modelClass)) {
				$this->itemClass = $this->dataProvider->query->modelClass;
			} else
				throw new InvalidConfigException('Manca itemClass');
		}
		if (!$this->isEmpty && !$this->columns) {
			if (empty($this->baseColumns)) {
				$this->columns = $this->itemClass::getGridViewColumns();
			} else {
				$this->columns = ArrayHelper::merge($this->baseColumns, $this->columns);
			}
			if ($this->mergeColumns) {
				$this->columns = ArrayHelper::merge($this->columns, $this->mergeColumns);
			}
		}
		if ($this->emptyText) {
			$this->showOnEmpty = true;
			$this->emptyText = '<p class="text-muted">' . Yii::t('app/' . $this->moduleId, $this->emptyText) . '</p>';
		}
		if ($this->summary) {
			$this->summary = '<h5>' . Yii::t('app/samuelelonghin/grid/summary', $this->summary) . '</h5>';
		}
		if ($this->rowClick && !$this->rowOptions) {
			if (!$this->rowClickUrl) {
				$this->rowClickUrl = '/' . $this->itemClass::getController() . '/view';
			}
			$urlClick = $this->rowClickUrl;
			$pk = $this->pk;
			if ($this->pk && !$this->attribute)
				$attribute = $this->pk;
			else
				$attribute = $this->attribute;
			$params = $this->rowClickParams;
			if (!$params)
				$params = [];
			$this->rowOptions = function ($model) use ($urlClick, $pk, $attribute, $params) {
				$params[$pk] = $model[$attribute];
				$params[] = $urlClick;
				$url = Url::toRoute($params);
				return [$pk => $model[$attribute], 'onclick' => 'cambiaPagina(event,"' . $url . '");'];
			};
		}
		if ($this->cornerButton === true) {
			$this->cornerButton = Btn::widget(['type' => 'expand', 'url' => $this->cornerButtonUrl ?: false, 'icon' => $this->cornerIcon ?: 'expand', 'text' => false]);
		}
		if ($this->collapse && $this->collapsable) {
			if (!isset($this->options['class']))
				$this->options['class'] = 'collapse';
			if (is_array($this->options['class']))
				array_push($this->options['class'], 'collapse');
			$this->options['class'] .= ' collapse';
		}


		//		var_dump($this->dataProvider->getPagination());
//		die();

		$this->prepareExport();
		parent::init();
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		if ($this->visible && (!$this->isEmpty || $this->emptyText)) {
			GridViewAsset::register($this->getView());
			$this->layout = '{top}{preGrid}{toggleData}' . $this->layout . '{postGrid}';
			if ($this->containerClass) {
				$this->layout = '{initContainer}' . $this->layout . '{endContainer}';
			}
			return parent::run();
		}
		return '';
	}

	public function renderInitContainer(): string
	{
		return Html::beginTag('div', ['class' => $this->containerClass]);
	}

	public function renderEndContainer(): string
	{
		return Html::endTag('div');
	}

	public function renderPreGrid(): string
	{
		return $this->preGrid;
	}

	public function renderPostGrid(): string
	{
		return $this->postGrid;
	}

	/**
	 * @return string
	 * @noinspection PhpUnusedPrivateMethodInspection
	 */
	private function renderTitle(): string
	{
		if (is_string($this->title)) {
			$headingNumber = 2 + $this->level;
			return Html::tag('h' . $headingNumber, Html::encode($this->title));
		}
		return '';
	}

	/**
	 * @throws InvalidConfigException
	 * @throws Exception
	 */
	protected function renderTop(): string
	{
		$top = '';
		if ($this->collapsable) {
			$top .= Html::tag('p', '...', ['class' => 'collapse' . !$this->collapse ? ' show' : '', 'id' => $this->id]);
			$top .= Html::a('', '#' . $this->id, ['class' => 'stretched-link', 'data-toggle' => 'collapse', 'role' => 'button', 'aria-expanded' => 'false', 'aria-controls' => $this->id]);
		}
		$title = $this->renderTitle();
		$cornerButtons = $this->renderCornerButtons();
		$exportButton = $this->renderExport();

		$alignEnd = $this->isBs(4) ? 'ml-auto' : ($this->isBs(5) ? 'align-self-end' : '');
		return Html::tag(
			'div',
			Html::tag('div', $title, ['class' => 'col']) .
			Html::tag('div', $cornerButtons . $exportButton, ['class' => "col-auto px-3 $alignEnd"]),
			['class' => 'row']
		);
	}

	protected function initModule()
	{
		if (!isset($this->moduleId)) {
			$this->_module = Module::getInstance();
			if (isset($this->_module)) {
				$this->moduleId = $this->_module->id;
				return;
			}
			$this->moduleId = Module::MODULE;
		}
		$this->_module = Config::getModule($this->moduleId, Module::class);
		if (isset($this->bsVersion)) {
			return;
		}
	}

	public function prepareExport()
	{
		if ($this->showExport !== false) {
			if (empty($this->exportColumns)) {
				$this->exportColumns = $this->columns;
			}
			if (!empty($this->exportMergeColumns) && is_array($this->exportMergeColumns)) {
				$this->exportColumns = ArrayHelper::merge($this->exportColumns, $this->exportMergeColumns);
			}
		}
	}

	/**
	 * @return string|null
	 * @throws Exception
	 */
	public function renderExport(): ?string
	{
		if ($this->showExport === false)
			return '';
		$filename = ArrayHelper::getValue($this->export, 'filename', $this->title);
		$showOnEmpty = ArrayHelper::getValue($this->export, 'showOnEmpty', $this->showOnEmpty);
		$showColumnSelector = ArrayHelper::getValue($this->export, 'showColumnSelector', true);
		$exportRequestParam = ArrayHelper::getValue($this->export, 'exportRequestParam', $this->id . '-export-');

		return ExportMenu::widget([
			'pjax' => false,
			'pjaxContainerId' => null,
			'clearBuffers' => true,
			'columns' => $this->exportColumns,
			'showOnEmpty' => $showOnEmpty,
			'filename' => $filename,
			'dataProvider' => $this->exportProvider,
			'showColumnSelector' => $showColumnSelector,
			'filterModel' => $this->filterModel,
			'exportRequestParam' => $exportRequestParam,
			'options' => ['id' => 'expMenu-' . $this->id],
			'boxStyleOptions' => [
				ExportMenu::FORMAT_HTML => $this->defaultExportStyle,
				ExportMenu::FORMAT_PDF => $this->defaultExportStyle,
				ExportMenu::FORMAT_EXCEL => $this->defaultExportStyle,
				ExportMenu::FORMAT_EXCEL_X => $this->defaultExportStyle,
			],
			'exportConfig' => [
				ExportMenu::FORMAT_HTML => [
					'defaultRowDimension' => ['height' => "200px"]
				],
				ExportMenu::FORMAT_PDF => [
					'pdfConfig' => [
						'cssFile' => '@webroot/css/pdf/main.css',
					],
					'config' => [
						'cssFile' => '@webroot/css/pdf/main.css',
					]
				],
			],
			'onRenderSheet' => function ($sheet) {
				/** @var Worksheet $sheet */
				$sheet->getStyle('A:Z')->getAlignment()->setWrapText(true);
			},
		]);
	}

	/**
	 * Renders a section of the specified name.
	 * If the named section is not supported, false will be returned.
	 * @param string $name the section name, e.g., `{summary}`, `{items}`.
	 * @return string|bool the rendering result of the section, or false if the named section is not supported.
	 */
	public function renderSection($name)
	{
		if (is_string($name) && !empty($name) && strlen($name) >= 3 && $name[0] == '{' && $name[strlen($name) - 1] == '}') {
			$first = strtoupper($name[1]);
			$rest = substr($name, 2, strlen($name) - 3);
			$renderFunction = 'render' . $first . $rest;
			if ($this->hasMethod($renderFunction))
				return $this->{$renderFunction}();
		}
		return parent::renderSection($name);
	}

	private function renderCornerButtons(): ?string
	{
		$out = '';
		if ($this->cornerButton)
			$out .= $this->cornerButton;
		return $out;
	}
}
