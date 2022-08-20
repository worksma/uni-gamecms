<div class="col-lg-3 order-is-last">
	{include file="/index/authorization.tpl"}
	{include file="/index/sidebar.tpl"}
</div>

{if($auth_api->steam_api == 1 || $auth_api->vk_api == 1 || $auth_api->fb_api == 1)}
<div class="col-lg-5">
{else}
<div class="col-lg-9">
{/if}
	<div class="block">
		<div class="block_head">
			Регистрация
		</div>
		
		<form id="user_registration" class="registration">
			<input type="text" maxlength="30" class="form-control" id="reg_login" placeholder="Логин">
			<input type="password" maxlength="15" class="form-control" id="reg_password" placeholder="Пароль">
			<input type="password" maxlength="15" class="form-control" id="reg_password2" placeholder="Повторите пароль">
			<input type="email" maxlength="255" class="form-control" id="reg_email" placeholder="E-mail {if($conf->conf_us == 1)}(Указывайте настоящий e-mail!){/if}">

			{if($conf->privacy_policy == 1)}
				<a class="privacy-policy" href="../processing-of-personal-data" target="_blank">Регистрируясь на данном сайте, Вы выражаете согласие на обработку персональных данных</a>
			{/if}

			{if($conf->captcha != '2')}
				<div style="transform:scale(0.75);-webkit-transform:scale(0.75);transform-origin:0 0;-webkit-transform-origin:0 0;" data-theme="light" class="g-recaptcha clearfix" data-sitekey="{{$conf->captcha_client_key}}"></div>
				<script src='https://www.google.com/recaptcha/api.js?hl=ru'></script>
			{/if}

			<div id="result2" class="text-center"></div>
			<button type="submit" class="btn btn-primary btn-block mt-2">Зарегистрироваться</button>
		</form>
		
		<div class="d-flex justify-content-center">
			<a href="/recovery" class="small">Забыли пароль?</a>
			<a href="/register" class="small ml-2">Регистрация</a>
		</div>
	</div>
</div>

{if($auth_api->steam_api == 1 || $auth_api->vk_api == 1 || $auth_api->fb_api == 1)}
<div class="col-lg-4">
	<div class="block">
		<div class="block_head">
			Сторонние сервисы
		</div>
		
		{if($auth_api->vk_api == 1)}
			<a class="btn btn-outline-primary" onclick="$('#registration').modal('hide'); show_reg_modal('vk');" title="Зарегистрироваться через Вконтакте"><i class="m-icon icon-vk"></i></a>
		{/if}
		{if($auth_api->steam_api == 1)}
			<a class="btn btn-outline-primary" onclick="$('#registration').modal('hide'); show_reg_modal('steam');" title="Зарегистрироваться через Steam"><i class="m-icon icon-steam"></i></a>
		{/if}
		{if($auth_api->fb_api == 1)}
			<a class="btn btn-outline-primary" onclick="$('#registration').modal('hide'); show_reg_modal('fb');" title="Зарегистрироваться через Facebook"><i class="m-icon icon-fb"></i></a>
		{/if}
	</div>
</div>
{/if}

<script> send_form('#user_registration', 'registration();'); </script>