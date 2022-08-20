<div class="block">
	<div class="block_head">
		Авторизация
	</div>

	<a class="btn btn-primary btn-block" href="/login">Войти на сайт</a>
	{if($auth_api->vk_api == 1)}
		<a class="btn btn-outline-primary btn-block" href="" id="vk_link" title="Войти через Вконтакте"><i class="m-icon icon-vk"></i> &nbsp Войти через ВК</a>
		<script>get_vk_auth_link();</script>
	{/if}
	<a class="btn btn-outline-primary btn-block" href="/register">Зарегистрироваться</a>
</div>