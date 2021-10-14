<?
	/*
		Подключение различных элементов
		///////////////////////////////
	*/
	
	$playground = new Playground($pdo, $conf);									// Система торговой площадки
	
	if(strpos($_SERVER['REQUEST_URI'], "profile") !== false) {
		global $profile;
		$bgimage = $playground->get_resource_active(1, $profile->id);
	}
?>
<div class="container-fluid wapper">
	<div class="content" <?
		if(isset($bgimage)) {
			echo "style=\"background:url('../files/playground/{$bgimage}') no-repeat;
			background-attachment: scroll;
			background-size: auto;
			background-attachment: fixed;
			background-size: cover;\"";
		}
	?>>
		<div class="header">
			<div class="container">
				<button class="menu-trigger btn d-block d-lg-none collapsed" type="button" data-toggle="collapse" data-target="#hidden-menu" ></button>
				<div class="clearfix d-block d-lg-none"></div>
				<div class="collapse d-none d-lg-block" id="hidden-menu">
					<ul class="collapsible-menu">
						{menu}
					</ul>
				</div>