<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Форум
		</div>
		<div id="forum">
			{func Forum:get_forums()}
		</div>
	</div>
</div>

<div class="col-lg-3 order-is-last">
	{if(is_worthy("t"))}
	<div class="block">
		<a href="../forum/edit_forum" class="btn btn-outline-primary btn-xl">Настройка форума</a>
	</div>
	{/if}

	{if(is_auth())}
		{include file="/home/navigation.tpl"}
		{include file="/forum/sidebar.tpl"}
	{else}
		{include file="/index/authorization.tpl"}
		{include file="/forum/sidebar.tpl"}
	{/if}
</div>