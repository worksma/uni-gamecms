<div class="col-lg-9 order-is-first">
	{func GetData:servers_admins({server})}
</div>
<div class="col-lg-3 order-is-last">
	{if(is_worthy("j"))}
	<div class="block">
		<a href="../edit_admins" class="btn btn-outline-primary btn-xl">Управление</a>
	</div>
	{/if}

	<div class="block">
		<div class="block_head">
			Сервера
		</div>
		<div class="vertical-navigation">
			<ul>
				{servers}
			</ul>
		</div>
	</div>

	{if(is_auth())}
		{include file="/home/navigation.tpl"}
		{include file="/home/sidebar_secondary.tpl"}
	{else}
		{include file="/index/authorization.tpl"}
		{include file="/home/sidebar_secondary.tpl"}
	{/if}
</div>