<div class="col-lg-9 order-is-first">
	<div class="block block-search">
		<div class="block_head">
			Пользователи
		</div>
		<div class="input-search">
			<i class="fas fa-search" onclick="search_login({start})"></i>
			<div class="input-group">
				<input type="text" class="form-control" id="search_login" placeholder="Введите логин пользователя">
				<select id="groups" class="form-control" onchange="group_change();">
					{groups}
				</select>
			</div>
		</div>

		<div id="users">
			{func GetData:users("{start}", "{group}", "{limit}")}
		</div>
	</div>

	<script>
		set_enter('#search_login', 'search_login({start})');

		function group_change() {
			var group = $('#groups').val();
			location.href = 'users?group='+group+'&page=1';
		}
	</script>

	<div id="pagination2">{pagination}</div>
</div>
<div class="col-lg-3 order-is-last">
	{if(is_auth())}
		{include file="/home/navigation.tpl"}
		{include file="/home/sidebar.tpl"}
	{else}
		{include file="/index/authorization.tpl"}
		{include file="/index/sidebar.tpl"}
	{/if}
</div>