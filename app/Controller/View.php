<?php
/**
 * Controller class for views.
 *
 * @package Controller
 *
 * @copyright YetiForce Sp. z o.o.
 * @license   YetiForce Public License 4.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Tomasz Kur <t.kur@yetiforce.com>
 * @author    Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 * @author    Radosław Skrzypczak <r.skrzypczak@yetiforce.com>
 */

namespace App\Controller;

/**
 * Controller class for views.
 */
abstract class View extends Base
{
	/** @var \App\Viewer Viewer object. */
	protected $viewer;

	/** {@inheritdoc} */
	public function __construct(\App\Request $request)
	{
		parent::__construct($request);
		$this->loadJsConfig($request);
		$this->viewer = new \App\Viewer();
		$this->viewer->assign('MODULE_NAME', $this->getModuleNameFromRequest($this->request));
		$this->viewer->assign('VIEW', $this->request->getByType('view', \App\Purifier::ALNUM));
		$this->viewer->assign('NONCE', \App\Session::get('CSP_TOKEN'));
		$this->viewer->assign('LANGUAGE', \App\Language::getLanguage());
		$this->viewer->assign('LANG', \App\Language::getShortLanguageName());
		$this->viewer->assign('USER', \App\User::getUser());
		$this->viewer->assign('ACTION_NAME', $this->request->getAction());
	}

	/**
	 * Check permission.
	 *
	 * @throws \App\Exceptions\NoPermitted
	 *
	 * @return void
	 */
	public function checkPermission(): void
	{
		$this->getModuleNameFromRequest($this->request);
		if (!\App\User::getUser()->isPermitted($this->moduleName)) {
			throw new \App\Exceptions\AppException('ERR_MODULE_PERMISSION_DENIED');
		}
	}

	/** {@inheritdoc} */
	public function preProcess($display = true): void
	{
		if ($this->loginRequired()) {
			$this->viewer->assign('MENU', \YF\Modules\Base\Model\Menu::getInstance($this->viewer->getTemplateVars('MODULE_NAME'))->getMenu());
		}
		$this->viewer->assign('PAGE_TITLE', (\Conf\Config::$siteName ?: \App\Language::translate('LBL_CUSTOMER_PORTAL')) . ' ' . $this->getPageTitle());
		$this->viewer->assign('CSS_FILE', $this->getHeaderCss());
		$this->viewer->assign('USER_QUICK_MENU', $this->getUserQuickMenuLinks());
		if ($display) {
			$this->preProcessDisplay();
		}
	}

	/**
	 * Get page title.
	 *
	 * @return string
	 */
	public function getPageTitle(): string
	{
		$title = \App\Language::translateModule($this->moduleName);
		$pageTitle = $this->getBreadcrumbTitle();
		if (isset($this->pageTitle)) {
			$pageTitle = \App\Language::translate($this->pageTitle);
		}
		if ($pageTitle) {
			$title .= ' - ' . $pageTitle;
		}
		return $title;
	}

	/**
	 * Get breadcrumb title.
	 *
	 * @return string
	 */
	public function getBreadcrumbTitle(): string
	{
		if (!empty($this->pageTitle)) {
			return $this->pageTitle;
		}
		return '';
	}

	/**
	 * Convert scripts path.
	 *
	 * @param array  $files
	 * @param string $fileExtension
	 *
	 * @return array
	 */
	public function convertScripts(array $files, string $fileExtension): array
	{
		$scriptsInstances = [];
		foreach ($files as $file) {
			[$fileName, $isOptional] = array_pad($file, 2, false);
			$path = ROOT_DIRECTORY . "/public_html/$fileName";
			$script = new \App\Script();
			$script->set('type', $fileExtension);
			$minFilePath = str_replace('.' . $fileExtension, '.min.' . $fileExtension, $fileName);
			if (\App\Config::$minScripts && file_exists(ROOT_DIRECTORY . "/public_html/$minFilePath")) {
				$scriptsInstances[] = $script->set('src', PUBLIC_DIRECTORY . $minFilePath . self::getTime($path));
			} elseif (file_exists($path)) {
				$scriptsInstances[] = $script->set('src', PUBLIC_DIRECTORY . $fileName . self::getTime($path));
			} elseif (!$isOptional) {
				\App\Log::warning('File not found: ' . $path);
			}
		}
		return $scriptsInstances;
	}

	/**
	 * Gets file modification time.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public static function getTime(string $path): string
	{
		$url = '';
		if ($fs = filemtime($path)) {
			$url = '?s=' . $fs;
		}
		return $url;
	}

	/**
	 * Retrieves css styles that need to loaded in the page.
	 *
	 * @return \App\Script[]
	 */
	public function getHeaderCss(): array
	{
		return $this->convertScripts([
			['libraries/@fortawesome/fontawesome-free/css/all.css'],
			['libraries/@mdi/font/css/materialdesignicons.css'],
			['libraries/chosen-js/chosen.css'],
			['libraries/bootstrap-chosen/bootstrap-chosen.css'],
			['libraries/jQuery-Validation-Engine/css/validationEngine.jquery.css'],
			['libraries/bootstrap-tabdrop/css/tabdrop.css'],
			['libraries/select2/dist/css/select2.css'],
			['libraries/datatables.net-bs4/css/dataTables.bootstrap4.css'],
			['libraries/datatables.net-responsive-bs4/css/responsive.bootstrap4.css'],
			['libraries/bootstrap-datepicker/dist/css/bootstrap-datepicker3.css'],
			['libraries/bootstrap-daterangepicker/daterangepicker.css'],
			['libraries/clockpicker/dist/bootstrap4-clockpicker.css'],
			['libraries/jstree-bootstrap-theme/dist/themes/proton/style.css'],
			['libraries/jstree/dist/themes/default/style.css'],
			['libraries/@pnotify/core/dist/PNotify.css'],
			['libraries/@pnotify/confirm/dist/PNotifyConfirm.css'],
			['libraries/@pnotify/bootstrap4/dist/PNotifyBootstrap4.css'],
			['libraries/@pnotify/mobile/dist/PNotifyMobile.css'],
			['libraries/@pnotify/desktop/dist/PNotifyDesktop.css'],
			['libraries/jquery-ui-dist/jquery-ui.css'],
			['libraries/perfect-scrollbar/css/perfect-scrollbar.css'],
			['layouts/resources/icons/additionalIcons.css'],
			['layouts/resources/icons/yfm.css'],
			['layouts/resources/icons/yfi.css'],
			['layouts/' . \App\Viewer::getLayoutName() . '/skins/basic/Main.css'],
		], 'css');
	}

	/**
	 * Pre process display.
	 *
	 * @return void
	 */
	protected function preProcessDisplay(): void
	{
		if (\App\Session::has('systemError')) {
			$this->viewer->assign('ERRORS', \App\Session::get('systemError'));
			\App\Session::unset('systemError');
		}
		$this->viewer->view($this->preProcessTplName(), $this->moduleName);
	}

	/**
	 * Get preprocess tpl name.
	 *
	 * @return string
	 */
	protected function preProcessTplName(): string
	{
		return 'Header.tpl';
	}

	/**
	 * Get process tpl name.
	 *
	 * @return string
	 */
	protected function processTplName(): string
	{
		return $this->request->getAction() . '/Index.tpl';
	}

	/** {@inheritdoc}  */
	protected function postProcessTplName(): string
	{
		return 'Footer.tpl';
	}

	/** {@inheritdoc} */
	public function postProcess(): void
	{
		if (null === $this->viewer->getTemplateVars('SHOW_FOOTER_BAR')) {
			$this->viewer->assign('SHOW_FOOTER_BAR', true);
		}
		$this->viewer->assign('JS_FILE', $this->getFooterScripts());
		if (\App\Config::$debugApi && \App\Session::has('debugApi') && \App\Session::get('debugApi')) {
			$this->viewer->assign('DEBUG_API', \App\Session::get('debugApi'));
			$this->viewer->view('DebugApi.tpl', $this->moduleName);
			\App\Session::set('debugApi', false);
		}
		$this->viewer->view($this->postProcessTplName(), $this->moduleName);
	}

	/**
	 * Scripts.
	 *
	 * @param bool $loadForModule
	 *
	 * @return \App\Script[]
	 */
	public function getFooterScripts(bool $loadForModule = true): array
	{
		$action = $this->request->getAction();
		$languageHandlerShortName = \App\Language::getShortLanguageName();
		$fileName = ["libraries/jQuery-Validation-Engine/js/languages/jquery.validationEngine-$languageHandlerShortName.js"];
		if (!file_exists(ROOT_DIRECTORY . "/public_html/{$fileName[0]}")) {
			$fileName = ['libraries/jQuery-Validation-Engine/js/languages/jquery.validationEngine-en.js'];
		}
		$files = [
			['libraries/jquery/dist/jquery.js'],
			['libraries/jquery.class.js/jquery.class.js'],
			['libraries/block-ui/jquery.blockUI.js'],
			['libraries/@pnotify/core/dist/PNotify.js'],
			['libraries/@pnotify/mobile/dist/PNotifyMobile.js'],
			['libraries/@pnotify/desktop/dist/PNotifyDesktop.js'],
			['libraries/@pnotify/confirm/dist/PNotifyConfirm.js'],
			['libraries/@pnotify/bootstrap4/dist/PNotifyBootstrap4.js'],
			['libraries/@pnotify/font-awesome5/dist/PNotifyFontAwesome5.js'],
			['libraries/perfect-scrollbar/dist/perfect-scrollbar.js'],
			['libraries/jquery-ui-dist/jquery-ui.js'],
			['libraries/popper.js/dist/umd/popper.js'],
			['vendor/ckeditor/ckeditor/ckeditor.js'],
			['vendor/ckeditor/ckeditor/adapters/jquery.js'],
			['libraries/bootstrap/dist/js/bootstrap.js'],
			['libraries/bootstrap-tabdrop/js/bootstrap-tabdrop.js'],
			['libraries/bootstrap-datepicker/dist/js/bootstrap-datepicker.js'],
			['libraries/moment/min/moment.min.js'],
			['libraries/bootstrap-daterangepicker/daterangepicker.js'],
			['libraries/bootbox/dist/bootbox.min.js'],
			['libraries/chosen-js/chosen.jquery.js'],
			['libraries/select2/dist/js/select2.full.js'],
			['libraries/inputmask/dist/jquery.inputmask.js'],
			['libraries/datatables.net/js/jquery.dataTables.js'],
			['libraries/datatables.net-bs4/js/dataTables.bootstrap4.js'],
			['libraries/datatables.net-responsive/js/dataTables.responsive.js'],
			['libraries/datatables.net-responsive-bs4/js/responsive.bootstrap4.js'],
			['libraries/jQuery-Validation-Engine/js/jquery.validationEngine.js'],
			$fileName,
			['libraries/jstree/dist/jstree.js'],
			['libraries/clockpicker/dist/bootstrap4-clockpicker.js'],
			['libraries/blueimp-file-upload/js/jquery.fileupload.js'],
			['layouts/resources/validator/BaseValidator.js'],
			['layouts/resources/validator/FieldValidator.js'],
			['layouts/resources/helper.js'],
			['layouts/resources/Field.js'],
			['layouts/resources/Connector.js'],
			['layouts/resources/app.js'],
			['layouts/resources/MultiImage.js'],
			['layouts/resources/Fields.js'],
			['layouts/resources/ProgressIndicator.js'],
			['layouts/' . \App\Viewer::getLayoutName() . '/modules/Base/resources/Header.js'],
			['layouts/' . \App\Viewer::getLayoutName() . "/modules/Base/resources/{$action}.js"],
		];
		if ($loadForModule) {
			$moduleName = $this->getModuleNameFromRequest();
			$files[] = ['layouts/' . \App\Viewer::getLayoutName() . "/modules/{$moduleName}/resources/{$action}.js", true];
		}
		return $this->convertScripts($files, 'js');
	}

	/** {@inheritdoc} */
	public function validateRequest()
	{
		$this->request->validateReadAccess();
	}

	/** {@inheritdoc} */
	public function postProcessAjax()
	{
	}

	/** {@inheritdoc} */
	public function preProcessAjax()
	{
	}

	/**
	 * Get module name from request.
	 *
	 * @return string
	 */
	protected function getModuleNameFromRequest(): string
	{
		if (empty($this->moduleName)) {
			$this->moduleName = $this->request->getModule();
		}
		return $this->moduleName;
	}

	/**
	 * Load js config.
	 *
	 * @param \App\Request $request
	 */
	public function loadJsConfig(\App\Request $request): void
	{
		$jsEnv = [
			'siteUrl' => \App\Utils::getPublicUrl('', true),
			'langPrefix' => \App\Language::getLanguage(),
			'langKey' => \App\Language::getShortLanguageName(),
			'parentModule' => $request->getByType('parent', 2),
		];
		foreach ($jsEnv as $key => $value) {
			\App\Config::setJsEnv($key, $value);
		}
	}

	/**
	 * Get the list of user quick menu links.
	 *
	 * @return array
	 */
	protected function getUserQuickMenuLinks(): array
	{
		$user = \App\User::getUser();
		$links = [
			[
				'label' => 'LBL_CHANGE_PASSWORD',
				'moduleName' => 'Users',
				'data' => ['url' => 'index.php?module=Users&view=PasswordChangeModal'],
				'icon' => 'yfi yfi-change-passowrd',
				'class' => 'text-decoration-none u-fs-sm text-secondary js-show-modal d-block',
				'btnClass' => ' ',
				'href' => '#',
				'showLabel' => true,
			],
			[
				'label' => 'BTN_YOUR_ACCOUNT_ACCESS_HISTORY',
				'moduleName' => 'Users',
				'data' => ['url' => 'index.php?module=Users&view=AccessActivityHistoryModal'],
				'icon' => 'yfi yfi-login-history',
				'class' => 'text-decoration-none u-fs-sm text-secondary js-show-modal d-block',
				'btnClass' => ' ',
				'href' => '#',
				'showLabel' => true,
			],
		];
		if ('PLL_PASSWORD_2FA' === $user->get('login_method') && !$user->isEmpty('authy_methods')) {
			$links[] = [
				'label' => 'BTN_2FA_TOTP_QR_CODE',
				'moduleName' => 'Users',
				'data' => ['url' => 'index.php?module=Users&view=TwoFactorAuthenticationModal'],
				'icon' => 'fas fa-key',
				'class' => 'text-decoration-none u-fs-sm text-secondary js-show-modal d-block',
				'btnClass' => ' ',
				'href' => '#',
				'showLabel' => true,
			];
		}
		return $links;
	}
}
