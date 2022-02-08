<div class="block">
	<div class="block_head">
		Авторизация
	</div>

	<button class="btn btn-primary btn-block" data-toggle="modal" data-target="#authorization">Войти на сайт</button>
	{if($auth_api->vk_api == 1)}
		<a class="btn btn-outline-primary btn-block" href="" id="vk_link" title="Войти через Вконтакте"><i class="m-icon icon-vk"></i> &nbsp Войти через ВК</a>
		<script>get_vk_auth_link();</script>
	{/if}
	<button class="btn btn-outline-primary btn-block" data-toggle="modal" data-target="#registration">Зарегистрироваться</button>
</div>