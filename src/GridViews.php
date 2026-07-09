<?php /** @noinspection PhpIllegalPsrClassPathInspection */

namespace samuelelonghin\grid;

use samuelelonghin\btn\Btn;
use yii\bootstrap5\Widget;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


class GridViews extends Widget
{
	public $data;
	public $isAssociative = false;
	public $itemClass = false;
	public $containerClass = 'rounded shadow mt-5 mb-5 p-3';
	public $level = 0;
	public $title = false;
	public $preGrid = false;
	public $postGrid = false;
	public $cornerButton = false;
	public $visible = true;
	public $limit = null;

	public function init()
	{
		if (isset($this->data) && $this->visible) {
			if (is_array($this->data)) {
				if (array_key_exists('_options', $this->data)) {
					$tempOptions = $this->data['_options'];
					if ($tempOptions['preGrid']) {
						$this->preGrid = $tempOptions['preGrid'];
					}
					if ($tempOptions['postGrid']) {
						$this->postGrid = $tempOptions['postGrid'];
					}
					if ($tempOptions['cornerButton']) {
						$icon = $tempOptions['cornerIcon'] ?: 'expand';
						$cornerButtonUrl = $tempOptions['cornerButtonUrl'];
						$this->cornerButton = Btn::widget(['type' => 'expand', 'url' => $cornerButtonUrl, 'icon' => $icon, 'text' => false]);
					}
					if ($tempOptions['limit']) {
						$this->limit = $tempOptions['limit'];
					}
					unset($this->data['_options']);
				}
			}
		}
		parent::init();
	}

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		if ($this->visible) {
			$this->renderPreGrid();
			$this->renderTitle();
			foreach ($this->data as $title => $options) {
				if (!$this->isAssociative) $title = ArrayHelper::getValue($options, 'title', '');
				$this->renderGrid($title, $options);
			}
			$this->renderPostGrid();
		}

	}

	public function renderPreGrid()
	{
		echo $this->preGrid ? $this->preGrid : '';
	}

	public function renderGrid($title, $options)
	{
		if (!array_key_exists('query', $options) && !array_key_exists('dataProvider', $options)) {
			if (!(isset($options['_options']) && isset($options['_options']['visible']) && !$options['_options']['visible'])) {
				$this->renderStartContainer();
				echo self::widget(['data' => $options, 'title' => Html::encode($title), 'containerClass' => '', 'level' => $this->level + 1, 'limit' => $this->limit]);
				$this->renderEndContainer();
			}
		} else {
			if (!(isset($options['visible']) && !$options['visible'])) {
				$this->renderStartContainer();
				$options['containerClass'] = '';
				$options['title'] = $title;
				$options['level'] = $this->level + 1;
				$options['limit'] = $this->limit;
				echo GridView::widget($options);
				$this->renderEndContainer();
			}
		}


	}

	private function renderTitle()
	{
		if (is_string($this->title) || $this->isAssociative) {
			$headingNumber = 2 + $this->level;
			?>
            <div class="row">
                <div class="col">
                    <h<?= $headingNumber ?>><?= Html::encode($this->title) ?></h<?= $headingNumber ?>>
                </div>
                <div class="px-3 ml-auto">
					<?= $this->cornerButton ?>
					<?php
					//                    $this->renderToggleButton();
					?>
                </div>
            </div>
			<?php
		}
	}

	public function renderPostGrid()
	{
		echo $this->postGrid ? $this->postGrid : '';
	}

	/** @noinspection PhpUnused */
	public function renderCornerButton()
	{
		if ($this->cornerButton) {
			?>
            <div class="row">
                <div class="px-3 ml-auto">
					<?= $this->cornerButton ?>
                </div>
            </div>
			<?php
		}
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function renderGridTitle($title)
	{
		if (is_string($title) || $this->isAssociative) {
			$headingNumber = 3 + $this->level;
			?>
            <div class="row">
                <div class="col">
                    <h<?= $headingNumber ?>><?= $title ?></h<?= $headingNumber ?>>
                </div>
                <div class="px-3 ml-auto">
					<?= $this->cornerButton ?>
                </div>
            </div>
			<?php
//            $this->title = false;
		}

	}

	public function renderToggleButton()
	{
		?>
        <button class="btn btn-link" role="button" data-toggle="collapse" data-target="#d-<?= $this->id ?>>"
                aria-expanded="true"
                aria-controls="d-<?= $this->id ?>">
            Apri/chiudi
        </button>
		<?php
	}

	public function renderStartContainer()
	{
		?>
        <div id="d-<?= $this->id ?>"
        class="collapse show <?= $this->level == 0 ? $this->containerClass : '' ?>">
		<?php
	}

	private function renderEndContainer()
	{
		?>
        </div>
		<?php
	}
}
