<?php

class Page
{
	const SCRIPT_ASSET = '1';
	const STYLE_ASSET  = '2';

	private $assets = [];
	private $breadCrumbs = [];
	private $title;
	private $description;
	private $keywords;
	private $image;

	public function __construct()
	{
		$this->title = page()->title;
		$this->description = page()->description;
		$this->keywords = page()->keywords;
		$this->image = page()->image;

		return $this;
	}

	public function isAdminPage() {
		return page()->type != 1;
	}

	public function setAsset($assetUrl, $assetType)
	{
		$this->assets[] = [
			'url'  => $assetUrl,
			'type' => $assetType
		];

		return $this;
	}

	public function collectPage($template, $data = [])
	{
		$this->collectTitle();
		$this->collectHead();

		if($this->isAdminPage()) {
			if(!is_admin()) {
				show_error_page('not_adm');
			}

			$this->collectAdminPage($template, $data);
		} else {
			if((page()->privacy == 1 || page()->privacy == 0) && !is_auth()){
				show_error_page('not_auth');
			}

			$this->collectUserPage($template, $data);
		}

		return $this;
	}

	public function setBreadCrumbs($breadCrumbs)
	{
		$this->breadCrumbs = $breadCrumbs;

		return $this;
	}

	public function setTitle($title)
	{
		$this->title = $title;

		return $this;
	}

	public function substituteToTitle($value)
	{
		$this->title = str_replace("{value}", $value, page()->title);

		return $this;
	}

	public function setDescription($description)
	{
		$this->description = $description;

		return $this;
	}

	public function substituteToDescription($description)
	{
		$this->title = str_replace("{value}", $description, page()->description);

		return $this;
	}

	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;

		return $this;
	}

	public function substituteToKeywords($keywords)
	{
		$this->title = str_replace("{value}", $keywords, page()->keywords);

		return $this;
	}

	public function setKeywordsFromString($string)
	{
		$this->keywords = str_replace(
			',,',
			',',
			str_replace(' ', ',', $string)
		);

		return $this;
	}

	public function setImage($image)
	{
		$this->image = $image;

		return $this;
	}

	private function getAssetHtml()
	{
		$assetsHtml = '';

		foreach($this->assets as $adminAsset) {
			if($adminAsset['type'] == self::SCRIPT_ASSET) {
				$assetsHtml .= '<script src="' . $adminAsset['url'] . '?v={cache}"></script>';
			}

			if($adminAsset['type'] == self::STYLE_ASSET) {
				$assetsHtml .= '<link rel="stylesheet" href="' . $adminAsset['url'] . '?v={cache}">';
			}
		}

		return $assetsHtml;
	}

	private function collectUserPage($templateFile, $data)
	{
		$nav = tpl()->get_nav(
			$this->breadCrumbs,
			'elements/nav_li.tpl',
			1
		);

		if(is_auth()) {
			include_once __DIR__ . '/../authorized.php';
		} else {
			include_once __DIR__ . '/../not_authorized.php';
		}

		$this->collectBody($templateFile, $data);
	}

	private function collectAdminPage($templateFile, $data)
	{
		tpl()->load_template('top.tpl');
		tpl()->set('{site_name}', configs()->name);
		tpl()->compile('content');
		tpl()->clear();

		tpl()->load_template('menu.tpl');
		tpl()->set('{site_host}', '../');
		tpl()->compile('content');
		tpl()->clear();

		$breadCrumbsNav = tpl()->get_nav(
			$this->breadCrumbs,
			'elements/nav_li.tpl',
			1
		);

		tpl()->load_template('page_top.tpl');
		tpl()->set('{nav}', $breadCrumbsNav);
		tpl()->compile('content');
		tpl()->clear();

		$this->collectBody($templateFile, $data);

		tpl()->load_template('bottom.tpl');
		tpl()->set('{site_host}', '../');
		tpl()->compile('content');
		tpl()->clear();
	}

	private function collectTitle()
	{
		tpl()->load_template('elements/title.tpl');
		tpl()->set('{title}', $this->title);
		tpl()->set('{name}', configs()->name);
		tpl()->compile('title');
		tpl()->clear();
	}

	private function collectHead()
	{
		tpl()->load_template('head.tpl');
		tpl()->set('{title}', tpl()->result['title']);
		tpl()->set('{site_name}', configs()->name);
		tpl()->set('{image}', $this->image);
		tpl()->set('{robots}', page()->robots);
		tpl()->set('{type}', page()->kind);
		tpl()->set('{description}', $this->description);
		tpl()->set('{keywords}', $this->keywords);
		tpl()->set('{url}', page()->full_url);
		tpl()->set('{other}', $this->getAssetHtml());
		tpl()->set('{token}', token());
		tpl()->set('{cache}', configs()->cache);
		tpl()->set('{template}', configs()->template);
		tpl()->set('{site_host}', '../');
		tpl()->compile('content');
		tpl()->clear();
	}

	private function collectBody($templateFile, $data = [])
	{
		if(page()->module) {
			$module = (new ExtraModule())->get(page()->module);

			if($this->isAdminPage()) {
				$templateDir = Template::DOWN_TO_ROOT . tpl()->getRelativeExtraModuleAdminDir($module->name);
			} else {
				$templateDir = Template::DOWN_TO_ROOT . tpl()->getRelativeExtraModuleDir($module->name);
			}

			$templateFile = $templateDir . $templateFile;
		}

		tpl()->load_template($templateFile);

		$data['site_host'] = '../';
		foreach($data as $key => $value) {
			tpl()->set('{' . $key . '}', $value);
		}

		tpl()->compile('content');
		tpl()->clear();
	}
}