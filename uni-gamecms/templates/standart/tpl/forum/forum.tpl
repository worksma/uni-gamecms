<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			{name}
		</div>
		<div id="forum_topics" class="row">
			{func Forum:get_forum("{id}","{start}","{limit}")}
		</div>
	</div>
	<div id="pagination2">{pagination}</div>
</div>

<div class="col-lg-3 order-is-last">
	{if(is_worthy("w"))}
	<div class="block">
		<a href="../forum/add_topic?id={id}" class="btn btn-outline-primary btn-xl">Открыть тему</a>
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