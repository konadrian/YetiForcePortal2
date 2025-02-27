<?php

/**
 * Tree view class.
 *
 * @package   View
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Arkadiusz Adach <a.adach@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace YF\Modules\Products\View;

use YF\Modules\Base\Model\ListView as ListViewModel;
use YF\Modules\Base\View;
use YF\Modules\Products\Model\Tree as TreeModel;

/**
 * Class Tree.
 */
class Tree extends View\ListView
{
	const CUSTOM_FIELDS = [
		'productname',
		'product_no',
		'ean',
		'category_multipicklist',
		'productcode',
		'unit_price',
		'taxes',
		'imagename',
		'description',
	];

	/** {@inheritdoc} */
	public function checkPermission(): void
	{
		parent::checkPermission();
		if (!\Conf\Modules\Products::$shoppingMode) {
			throw new \App\Exceptions\AppException('ERR_MODULE_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function process()
	{
		$this->page = $this->request->getInteger('page', 1);
		$offset = ($this->page - 1) * (\App\Config::$itemsPrePage ?: 15);
		$this->getListViewModel()
			->setRawData(true)
			->setFields(static::CUSTOM_FIELDS)
			->setPage($this->page)
			->setOffset($offset);
		$search = [];
		$searchText = '';
		if ($this->request->has('search') && !$this->request->isEmpty('search')) {
			$search = $this->request->get('search');
			foreach ($search as &$condition) {
				if ('productname' === $condition['fieldName']) {
					$condition['group'] = false;
					$search[] = [
						'fieldName' => 'ean',
						'value' => $condition['value'],
						'operator' => 'c',
						'group' => false,
					];
				}
			}
			$this->getListViewModel()->setConditions($search);
		}
		$this->viewer->assign('SEARCH_TEXT', $searchText);
		$this->viewer->assign('SEARCH', $search);
		$this->viewer->assign('CHECK_STOCK_LEVELS', \App\User::getUser()->get('companyDetails')['check_stock_levels'] ?? false);
		$this->viewer->assign('RECORDS', $this->getListViewModel()->getRecordsListModel());
		$this->viewer->assign('LIST_VIEW_MODEL', $this->getListViewModel());
		$this->viewer->assign('HEADERS', []);
		$this->viewer->view($this->processTplName(), $this->moduleName);
	}

	/** {@inheritdoc} */
	public function preProcess($display = true): void
	{
		$moduleName = $this->request->getModule();
		$fields = \App\Api::getInstance()
			->setCustomHeaders(['x-response-params' => '["blocks", "privileges"]'])
			->call('Products/Fields') ?: [];
		$searchInfo = [];
		if ($this->request->has('search') && !$this->request->isEmpty('search')) {
			foreach ($this->request->get('search') as $condition) {
				$searchInfo[$condition['fieldName']] = $condition['value'];
			}
		}
		$this->viewer->assign('SEARCH_TEXT', $searchInfo['productname'] ?? '');
		$this->viewer->assign('LEFT_SIDE_TEMPLATE', 'Tree/Category.tpl');
		$this->viewer->assign(
			'TREE',
			TreeModel::getInstance($moduleName, 'Tree')
				->setFields($fields)
				->setSelectedItems([$searchInfo['category_multipicklist'] ?? null])
				->getTree()
		);
		$filterFields = [];
		$filterFieldsName = \App\Config::get('filterInProducts', []);
		foreach ($fields['fields'] as $field) {
			if (\in_array($field['name'], $filterFieldsName)) {
				$fieldInstance = \YF\Modules\Base\Model\Field::getInstance($moduleName, $field);
				$fieldInstance->setRawValue($searchInfo[$field['name']] ?? '');
				$filterFields[] = $fieldInstance;
			}
		}
		$this->viewer->assign('FILTER_FIELDS', $filterFields);
		parent::preProcess($display);
	}

	/** {@inheritdoc} */
	protected function processTplName(): string
	{
		return $this->request->getAction() . '/Tree.tpl';
	}

	/** {@inheritdoc} */
	protected function preProcessTplName(): string
	{
		return $this->request->getAction() . '/TreePreProcess.tpl';
	}

	/** {@inheritdoc} */
	protected function postProcessTplName(): string
	{
		return $this->request->getAction() . '/TreePostProcess.tpl';
	}

	/** {@inheritdoc} */
	protected function getListViewModel(): ListViewModel
	{
		if (empty($this->listViewModel)) {
			$this->listViewModel = ListViewModel::getInstance($this->moduleName, 'TreeView');
		}
		return $this->listViewModel;
	}
}
