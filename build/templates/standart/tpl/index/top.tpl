<?
	$request_url = $_SERVER['REQUEST_URI'];
	if(strpos($_SERVER['REQUEST_URI'], "profile") !== false):
		global $profile;
		$background = trading()->get_resource_active(1, $profile->id);
	endif;
?>
<div class="container-fluid wapper">
	{if(isset($background))}
	<div class="content" style="background: url('/files/playground/{{$background}}') no-repeat; background-attachment: scroll; background-attachment: fixed; background-size: cover;">
	{else}
	<div class="content">
	{/if}
		<div class="header">
			<div class="container">
				<a class="auth-in btn btn-outline-primary btn-sm" href="/login">Войти на сайт</a>
				<button class="menu-trigger btn d-block d-lg-none collapsed" type="button" data-toggle="collapse" data-target="#hidden-menu" ></button>
				<div class="clearfix d-block d-lg-none"></div>
				<div class="collapse d-none d-lg-block" id="hidden-menu">
					<ul class="collapsible-menu">
						{menu}
					</ul>
				</div>
			</div>
		</div>
		<div class="navigation">
			<div class="container">
				<ul class="breadcrumb">
					{page_name}
				</ul>			
			</div>
		</div>
	
		<div class="container">
			{include file="parts/page_head.tpl"}
			<div id="api_auth" class="modal fade">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title">Регистрация</h4>
						</div>
						<div class="modal-body">
							<p>Укажите, пожалуйста, свой e-mail.</p>

							<input type="email" maxlength="255" class="form-control" id="api_email" placeholder="E-mail {if($conf->conf_us == 1)}(Требуется настоящий e-mail!){/if}">
							
							<div id="result_api_reg"></div>
							<button id="api_reg_btn" class="btn btn-primary btn-block mt-2" onclick="">Зарегистрироваться</button>
						</div>
					</div>
				</div>
			</div>

			<div class="row">