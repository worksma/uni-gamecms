<?php
class Template {
	const ADMIN_TEMPLATE_DIR = 'templates/admin/tpl/';
	const CLIENT_TEMPLATE_DIR = 'templates/{template}/tpl/';
	const DOWN_TO_ROOT = '../../../';

	public  $dir = '.'; 
	public  $sec_dir = ''; 
	public  $template = null; 
	public  $copy_template = null; 
	public  $data = array();
	public  $result = array('info' => '', 'content' => '');
	public  $modules_tpls = array();
	public  $files = '';
	public  $caching = 0;
	private $includeLimit = 0;
	protected $using_tpl = '';
	protected $templater_preg = array(
		'/{\*.*?\*}/is' => '', // comment: {* some comment text *}

		'/{ ?if ?\( ?([^;<].[^;<]{1,250}?) ?\) ?}/' => '<?php if(${1}): ?>', // condition: {if(true)} 1 {else} 2 {/if}
		'/{ ?else ?}/' => '<?php else: ?>',

		'/{ ?for ?\( ?([^;<].[^;<]{1,50}?) ?; ?([^;].[^;]{1,50}?) ?; ?([^;<].[^;<]{1,50}?) ?\) ?}/' => '<?php for(${1}; ${2}; ${3}): ?>', // cycle: {for($i=0;$i<$num;$i++)} {{$i}} {/for}
		'/{ ?\/(for|if) ?}/' => '<?php end${1}; ?>',

		'/{{ ?([a-zA-Z0-9>,\(\)_\-\]\[\'"$]{1,50}) ?}}/' => '<?php echo ${1}; ?>', // echo var: {{$var}}

		'/{ ?func ([a-zA-Z0-9_]{1,30}):([a-zA-Z0-9_]{1,50})\( ?([^;<].[^;<]{0,150}?)? ?\) ?}/' => '<?php if (class_exists("${1}")) { $CE = new ${1}($pdo, $tpl); if(method_exists($CE, "${2}")) { $tpl->show($CE->${2}(${3})); } unset($CE); } ?>', // do function 

		'/{ ?sql select\( ?([a-zA-Z0-9_\-$]{1,50}) ?, ?([a-zA-Z0-9_\-$]{1,50}) ?, ?\'([a-zA-Z0-9 _\-]{1,150})\' ?, ?\'([a-zA-Z0-9_\-]{1,50})\' ?, ?\'(.{1,100})\' ?, ?([0-9]{0,5}) ?\) ?}/' => '<?php ${1} = db_get_info(${2}, "${3}", "${4}", \'${5}\', ${6}); ?>', // sql select 
	);
	protected $config_preg = array(
		'/{ ?configuration ?}.*?{ ?\/configuration ?}/is', // configuration: {configuration} ... {/configuration}
		'/{ ?var:([a-zA-Z0-9_\'\]\[]{1,30}) ?}(.*?){ ?\/var ?}/is', // variable: {var:name} value {/var}
	);

	function __construct() {
		global $conf;
		if(isset($conf->caching) && $conf->caching == 1) {
			$this->caching = 1;
		}
	}

	public function set($name , $var) { 
		if (is_array($var) && count($var)) { 
			foreach ($var as $key => $key_var) { 
				$this->set($key , $key_var); 
			}
		} else {
			$this->data[$name] = $var;
		}

		return $this;
	}

	public function setCoreDir() {
		$this->dir = $this->getCoreDir();
	}

	public function setCoreAdminDir() {
		$this->dir = $this->getCoreAdminDir();
	}

	public function setExtraModuleDir($moduleName) {
		$this->dir = $this->getExtraModuleDir($moduleName);
	}

	public function setExtraModuleAdminDir($moduleName) {
		$this->dir = $this->getExtraModuleAdminDir($moduleName);
	}

	public function getCoreDir() {
		return __DIR__ . '/../../' . $this->getRelativeCoreDir();
	}

	public function getCoreAdminDir() {
		return __DIR__ . '/../../' . $this->getRelativeCoreAdminDir();
	}

	public function getExtraModuleDir($moduleName) {
		return __DIR__ . '/../../' . $this->getRelativeExtraModuleDir($moduleName);
	}

	public function getExtraModuleAdminDir($moduleName) {
		return __DIR__ . '/../../' . $this->getRelativeExtraModuleAdminDir($moduleName);
	}

	public function getRelativeCoreDir() {
		return str_replace('{template}', configs()->template, self::CLIENT_TEMPLATE_DIR);
	}

	public function getRelativeCoreAdminDir() {
		return self::ADMIN_TEMPLATE_DIR;
	}

	public function getRelativeExtraModuleDir($moduleName) {
		return 'modules_extra/' . $moduleName . '/' . str_replace('{template}', configs()->template, self::CLIENT_TEMPLATE_DIR);
	}

	public function getRelativeExtraModuleAdminDir($moduleName) {
		return 'modules_extra/' . $moduleName . '/' . self::ADMIN_TEMPLATE_DIR;
	}

	public function replace_preg($data) {
		foreach ($this->templater_preg as $preg=>$replace){
			$data = preg_replace($preg, $replace, $data);
		}
		return $data;
	}

	public function load_template($tpl_name) {
		if(isset($this->modules_tpls)) {
			$tpl_name = $this->replace_tpl($tpl_name);
		}
		if ($tpl_name == '' || !file_exists($this->sec_dir.$this->dir.$tpl_name)) { 
			die ("[Class Template]: Unable to load template: ". $this->sec_dir.$this->dir.$tpl_name); 
			return false;
		} 
		$this->using_tpl = $tpl_name;

		if($this->caching) {
			$cache_file = $this->cache_file_name($this->sec_dir.$this->dir.'../cache/'.$this->using_tpl);
			$orig_time = filemtime($this->sec_dir.$this->dir.$this->using_tpl);
			if(file_exists($cache_file)) {
				$cache_time = filemtime($cache_file);
				if($cache_time > $orig_time) {
					return true; 
				}
			}
		}

		$this->template = file_get_contents($this->sec_dir.$this->dir.$tpl_name); 
		
		if(($tpl_name != 'elements/title.tpl') && isset($_SESSION['dev_mode']) && ($_SESSION['dev_mode'] == 1)) {
			$this->template = "\n<!-- Start ".$tpl_name." -->\n".$this->template."\n<!-- End ".$tpl_name." -->\n";
		}
		
		if ( stristr( $this->template, "{include file=" ) ) {
			$this->includeLimit = 0;
			$this->template = preg_replace_callback( "#\\{include file=['\"](.+?)['\"]\\}#is", "self::sub_load_template", $this->template);
		}

		$this->copy_template = $this->template;
		return $this; 
	}

	public function _clear() { 
		$this->data = array(); 
		$this->copy_template = $this->template;
	} 

	public function clear() { 
		$this->data = array();
		$this->copy_template = null; 
		$this->template = null; 
		$this->using_tpl = '';

		return $this;
	}
	
	public function global_clear() { 
		$this->data = array(); 
		$this->result = array(); 
		$this->copy_template = null; 
		$this->template = null; 
		$this->sec_dir = ''; 
		$this->modules_tpls = array();
		$this->files = '';
		$this->using_tpl = '';
	}

	private function cache_file_name($name) { 
		$path = explode('cache/', $name);
		$path[1] = str_replace('../../../modules_extra/', '', $path[1]);
		$path[1] = str_replace('/', '__', $path[1]);
		return $path[0].'cache/'.$path[1];
	}

	public function compile($tpl) {
		$cache_time = 0;
		$orig_time = 1;
		if($this->caching) {
			$cache_file = $this->cache_file_name($this->sec_dir.$this->dir.'../cache/'.$this->using_tpl);
			$orig_time = filemtime($this->sec_dir.$this->dir.$this->using_tpl);
			if(file_exists($cache_file)) {
				$cache_time = filemtime($cache_file);
			}
		}

		if($cache_time > $orig_time) {
			$result = file_get_contents($cache_file); 
		} else {
			$result = $this->replace_preg($this->copy_template);

			if($this->caching && @$cache_file = fopen($cache_file, "w+")) {
				fwrite($cache_file, $result);
				fclose($cache_file); 		
			}
		}

		if($this->using_tpl == 'head.tpl') {
			$find = array('{files}');
			$replace = array($this->files);
		} else {
			$find[] = 0;	
			$replace[] = 0;	
		}

		foreach ($this->data as $key_find => $key_replace) { 
			$find[] = $key_find; 
			$replace[] = $key_replace; 
		}
		$result = str_replace($find, $replace, $result); 

		if (isset($this->result[$tpl])) {
			$this->result[$tpl] .= $result; 
		} else {
			$this->result[$tpl] = $result; 
		}
		$this->_clear();

		return $this;
	}

	public function show($tpl_data_) {
		foreach($GLOBALS as $key=>$val){
			global $$key;
		}
		eval(' ?>'.$tpl_data_.'<?php ');
	}

	public function getShow($tpl_data_) {
		ob_start();

		foreach($GLOBALS as $key=>$val){
			global $$key;
		}
		eval(' ?>'.$tpl_data_.'<?php ');

		return ob_get_clean();
	}

	public function get_nav($array, $tpl_name, $point = 0) {
		$count = count($array);
		for ($i=0; $i < $count; $i++) { 
			$this->load_template($tpl_name);
			$this->set("{href}", $array[$i][0]);
			$this->set("{name}", $array[$i][1]);
			$this->compile( 'nav' );
			$this->clear();
		}

		ob_start();
		eval('?>'.$this->result['nav'].'<?php '); 
		$this->result['nav'] = ob_get_clean();

		return $this->result['nav'];
	}

	/*
	$page - номер текущей страницы
	$number - общее количество элементов
	$limit - количество элементов на страницу
	$stages = 3
	$page_name - начало url'a страницы
	*/
	public function get_paginator($page,$number,$limit,$stages,$page_name) {
		if ($page == 0){
			$page = 1;
		}
		if(empty($number)) {
			$number = 0;
		}
		$prev = $page - 1;
		$next = $page + 1;
		$lastpage = ceil($number/$limit);
		$lastpage2 = $lastpage - 1;

		$paginate = '';
		if($lastpage > 1){
			$paginate .= "<ul class='pagination'>";
			if ($page > 1){
				$paginate.= "<li><a href='".$page_name."page=".$prev."'><span aria-hidden='true'>&laquo;</span><span class='sr-only'>Назад</span></a></li>";
			} else {
				$paginate.= "<li class='disabled'><a><span aria-hidden='true'>&laquo;</span><span class='sr-only'>Назад</span></a></li>";
			}

			if ($lastpage < 7 + ($stages * 2)){ 
				for ($counter = 1; $counter <= $lastpage; $counter++){
					if ($counter == $page){
						$paginate.= "<li class='active'><a>$counter</a></li>";
					} else {
						$paginate.= "<li><a href='".$page_name."page=".$counter."'>$counter</a></li>";
					}
				}
			} elseif($lastpage > 5 + ($stages * 2)){
				if($page < 1 + ($stages * 2)){
					for ($counter = 1; $counter < 4 + ($stages * 2); $counter++){
						if ($counter == $page){
							$paginate.= "<li class='active'><a>$counter</a></li>";
						} else {
							$paginate.= "<li><a href='".$page_name."page=".$counter."'>$counter</a></li>";
						}
					}
					$paginate.= "<li><a>...</a></li>";
					$paginate.= "<li><a href='".$page_name."page=$lastpage2'>$lastpage2</a></li>";
					$paginate.= "<li><a href='".$page_name."page=$lastpage'>$lastpage</a></li>";
				} elseif($lastpage - ($stages * 2) > $page && $page > ($stages * 2)){
					$paginate.= "<li><a href='".$page_name."page=1'>1</a></li>";
					$paginate.= "<li><a href='".$page_name."page=2'>2</a></li>";
					$paginate.= "<li><a>...</a></li>";
					for ($counter = $page - $stages; $counter <= $page + $stages; $counter++){
						if ($counter == $page){
							$paginate.= "<li class='active'><a>$counter</a></li>";;
						} else {
							$paginate.= "<li><a href='".$page_name."page=".$counter."'>$counter</a></li>";
						}
					}
					$paginate.= "<li><a>...</a></li>";
					$paginate.= "<li><a href='".$page_name."page=".$lastpage2."'>$lastpage2</a></li>";
					$paginate.= "<li><a href='".$page_name."page=".$lastpage."'>$lastpage</a></li>";
				} else {
					$paginate.= "<li><a href='".$page_name."page=1'>1</a></li>";
					$paginate.= "<li><a href='".$page_name."page=2'>2</a></li>";
					$paginate.= "<li><a>...</a></li>";
					for ($counter = $lastpage - (2 + ($stages * 2)); $counter <= $lastpage; $counter++){
						if ($counter == $page){
							$paginate.= "<li class='active'><a>$counter</a></li>";
						} else {
							$paginate.= "<li><a href='".$page_name."page=".$counter."'>$counter</a></li>";
						}
					}
				}
			}

			if ($page < $counter - 1){ 
				$paginate.= "<li><a href='".$page_name."page=".$next."'><span aria-hidden='true'>&raquo;</span><span class='sr-only'>Вперед</span></a></li>";
			} else {
				$paginate.= "<li class='disabled'><a><span aria-hidden='true'>&raquo;</span><span class='sr-only'>Вперед</span></a></li>";
			}
			$paginate.= "</ul>";
		}
		return $paginate;
	}

	public function get_menu($pdo){
		$menu = '';
		$STH = $pdo->query("SELECT * FROM `menu` ORDER BY `poz`");
		$STH->execute();
		$temp = $STH->fetchAll();
		$count_temp = count($temp);

		if ($count_temp != 0){
			for($i_temp=0; $i_temp<$count_temp; $i_temp++){
				if($temp[$i_temp]['menu__sub']!='0'){
					$id=$temp[$i_temp]['id'];
					$STH = $pdo->query("SELECT * FROM `menu__sub` WHERE `menu`='$id' ORDER BY `poz`");
					$STH->execute();
					$temp2 = $STH->fetchAll();
					$count_temp2 = count($temp2);

					if (($count_temp2 != 0) and ($temp[$i_temp]['for_all'] == 1) or ($temp[$i_temp]['for_all'] == 2 and isset($_SESSION['id'])) or ($temp[$i_temp]['for_all'] == 3 and empty($_SESSION['id']))){
						$menu.= '<li class="collapsible"><a href="">'.$temp[$i_temp]['name'].'</a><ul>';
						for($i_temp2=0; $i_temp2<$count_temp2; $i_temp2++){
							if(($temp2[$i_temp2]['for_all'] == 1) or ($temp2[$i_temp2]['for_all'] == 2 and isset($_SESSION['id'])) or ($temp2[$i_temp2]['for_all'] == 3 and empty($_SESSION['id']))) {
								if($temp2[$i_temp2]['link'] == '../profile' && isset($_SESSION['id'])) {
									$temp2[$i_temp2]['link'] = '../profile?id='.$_SESSION['id'];
								}
								$menu.='<li><a href="'.$temp2[$i_temp2]['link'].'">'.$temp2[$i_temp2]['name'].'</a></li>';
							}
						}
						$menu.='</ul></li>';
					}
				} else {
					if(($temp[$i_temp]['for_all'] == 1) or ($temp[$i_temp]['for_all'] == 2 and isset($_SESSION['id'])) or ($temp[$i_temp]['for_all'] == 3 and empty($_SESSION['id']))) {
						if($temp[$i_temp]['link'] == '../profile') {
							$temp[$i_temp]['link'] = '../profile?id='.$_SESSION['id'];
						}
						$menu.= '<li><a href="'.$temp[$i_temp]['link'].'">'.$temp[$i_temp]['name'].'</a></li>';
					}
				}
			} 
		}
		return $menu;
	}

	public function dell_cache($template = null) {
		if($template != null) {
			if(file_exists($_SERVER["DOCUMENT_ROOT"]."/templates/".$template."/tpl/head.tpl")) {
				removeDirectory($_SERVER["DOCUMENT_ROOT"]."/templates/".$template."/cache/", 2);
			}
		} else {
			$folders = scandir($_SERVER["DOCUMENT_ROOT"]."/templates/");
			for ($i=2; $i < count($folders); $i++) {
				if(file_exists($_SERVER["DOCUMENT_ROOT"]."/templates/".$folders[$i]."/tpl/head.tpl")) {
					removeDirectory($_SERVER["DOCUMENT_ROOT"]."/templates/".$folders[$i]."/cache/", 2);
				}
			}
		}

		return true;
	}

	private function replace_tpl($tpl_name) {
		$this->sec_dir = '';
		for ($i=0; $i < count($this->modules_tpls); $i++) {
			for ($j=0; $j < count($this->modules_tpls[$i]); $j++) {
				if($tpl_name == $this->modules_tpls[$i][$j][0]) {
					$this->sec_dir = "modules_extra/".$this->modules_tpls[$i][0]."/";
					$tpl_name = $this->modules_tpls[$i][$j][1];
					break(2);
				}
			}
		}
		return $tpl_name;
	}

	private function sub_load_template($tpl_name) {
		$this->includeLimit++;

		if($this->includeLimit == 10) {
			return '';
		}

		if ($tpl_name[1] == '' || !file_exists($this->dir.'/'.$tpl_name[1])) {
			die ("[Class Template]: Unable to load template: ". $tpl_name[1]);
		}

		$template = file_get_contents($this->dir.'/'.$tpl_name[1]);

		if ( stristr( $template, "{include file=" ) ) {
			$template = preg_replace_callback( "#\\{include file=['\"](.+?)['\"]\\}#is", "self::sub_load_template", $template);
		} else {
			if(($tpl_name[1] != 'elements/title.tpl') && isset($_SESSION['dev_mode']) && ($_SESSION['dev_mode'] == 1)) {
				$template = "\n<!-- Start ".$tpl_name[1]." -->\n".$template."\n<!-- End ".$tpl_name[1]." -->\n";
			}
			if($tpl_name[1] == 'config.tpl') {
				if(preg_match($this->config_preg[0], $template)) {

					preg_match_all($this->config_preg[1], $template, $matches, PREG_SET_ORDER);

					$template = '<?php'."\n";
					for ($i = 0; $i < count($matches); $i++) {
						$template .= '$'.$matches[$i][1].' = \''.str_replace("'", "\'", $matches[$i][2]).'\';'."\n";
					}
					$template .= "\n".'?>';
				}
			}
		}

		return $template;
	}
}