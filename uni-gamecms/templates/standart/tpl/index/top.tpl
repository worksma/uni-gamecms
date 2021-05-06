<div class="container-fluid wapper">
	<div class="content">
		<div class="header">
			<div class="container">
				<button class="auth-in btn btn-outline-primary btn-sm" data-toggle="modal" data-target="#authorization">Войти на сайт</button>
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
			<div class="row">
				<div class="col-lg-3">
					<div class="logo">
						<a href="{site_host}" title="{site_name}">
							<img src="{site_host}templates/{template}/img/logo.png" alt="{site_name}">
						</a>
					</div>
				</div>
				<div class="col-lg-9 px-0 px-lg-3">
					<div class="monitoring">
						<div id="servers">
							<div class="loader"></div>
							<script>get_servers2();</script>
						</div>
					</div>
				</div>
			</div>

			<div id="authorization" class="modal fade">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title">Авторизация</h4>
						</div>
						<div class="modal-body">
							<form id="user_login" class="authorization">
								<input type="text" maxlength="30" class="form-control" id="user_loginn" placeholder="Логин">
								<input type="password" maxlength="15" class="form-control" id="user_password" placeholder="Пароль">
								<button type="submit" class="btn btn-primary btn-block">Войти</button>

								<div id="result" class="disp-n">{conf_mess}</div>

								<a href="../recovery" class="small">Забыли пароль?</a>
								<a class="small ml-2" onclick="$('#authorization').modal('hide');" href="#" data-toggle="modal" data-target="#registration">Регистрация</a><br>

								{if($auth_api->steam_api == 1)}
									<a class="btn btn-outline-primary" href="" id="steam_link" title="Войти через Steam"><i class="m-icon icon-steam"></i></a>
									<script>get_steam_auth_link();</script>
								{/if}
								{if($auth_api->vk_api == 1)}
									<a class="btn btn-outline-primary" href="" id="vk_link" title="Войти через Вконтакте"><i class="m-icon icon-vk"></i></a>
									<script>get_vk_auth_link();</script>
								{/if}
								{if($auth_api->fb_api == 1)}
									<a class="btn btn-outline-primary" href="" id="fb_link" title="Войти через Facebook"><i class="m-icon icon-fb"></i></a>
									<script>get_fb_auth_link();</script>
								{/if}
							</form>
							<script> send_form('#user_login', 'user_login();'); </script>
						</div>
					</div>
				</div>
			</div>

			<div id="registration" class="modal fade">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title">Регистрация</h4>
						</div>
						<div class="modal-body">
							<form id="user_registration" class="registration">
								<input type="text" maxlength="30" class="form-control" id="reg_login" placeholder="Логин">
								<input type="password" maxlength="15" class="form-control" id="reg_password" placeholder="Пароль">
								<input type="password" maxlength="15" class="form-control" id="reg_password2" placeholder="Повторите пароль">
								<input type="email" maxlength="255" class="form-control" id="reg_email" placeholder="E-mail {if($conf->conf_us == 1)}(Указывайте настоящий e-mail!){/if}">

								{if($conf->privacy_policy == 1)}
									<a class="privacy-policy" href="../processing-of-personal-data" target="_blank">Регистрируясь на данном сайте, Вы выражаете согласие на обработку персональных данных</a>
								{/if}

								{if($conf->captcha != '2')}
									<div style="transform:scale(0.75);-webkit-transform:scale(0.75);transform-origin:0 0;-webkit-transform-origin:0 0;" data-theme="light" class="g-recaptcha clearfix" data-sitekey="{{$conf->captcha}}"></div>
									<script src='https://www.google.com/recaptcha/api.js?hl=ru'></script>
								{/if}

								<div id="result2"></div>

								<button type="submit" class="btn btn-primary btn-block mt-2">Зарегистрироваться</button>

								{if($auth_api->vk_api == 1)}
									<a class="btn btn-outline-primary" onclick="$('#registration').modal('hide'); show_reg_modal('vk');" title="Зарегистрироваться через Вконтакте"><i class="m-icon icon-vk"></i></a>
								{/if}
								{if($auth_api->steam_api == 1)}
									<a class="btn btn-outline-primary" onclick="$('#registration').modal('hide'); show_reg_modal('steam');" title="Зарегистрироваться через Steam"><i class="m-icon icon-steam"></i></a>
								{/if}
								{if($auth_api->fb_api == 1)}
									<a class="btn btn-outline-primary" onclick="$('#registration').modal('hide'); show_reg_modal('fb');" title="Зарегистрироваться через Facebook"><i class="m-icon icon-fb"></i></a>
								{/if}
							</form>
							<script> send_form('#user_registration', 'registration();'); </script>
						</div>
					</div>
				</div>
			</div>

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