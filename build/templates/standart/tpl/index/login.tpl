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
			Авторизация
		</div>
		
		<form id="user_login" class="authorization">
			<input id="user_loginn" type="text" class="form-control" placeholder="Логин">
			<input id="user_password" type="password" class="form-control" placeholder="Пароль">
			
			<button type="submit" class="btn btn-primary btn-block">Войти</button>
			<div id="result" class="disp-n text-center">{conf_mess}</div>
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
	</div>
</div>
{/if}

<script> send_form('#user_login', 'user_login();'); </script>