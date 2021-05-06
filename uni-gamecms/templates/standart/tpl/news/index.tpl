<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Новости проекта
		</div> 
		<div class="vertical-center-line">
			<div class="clearfix news">
				{func GetData:news("{start}","{class}","{limit}")}
			</div>
		</div>
	</div>
	
	<div id="pagination2">{pagination}</div>
</div>
<div class="col-lg-3 order-is-last">
	{if(is_worthy("b"))}
	<div class="block">
		<a href="../news/add_new" class="btn btn-outline-primary btn-xl">Добавить новость</a>
	</div>
	{/if}

	<div class="block">
		<div class="block_head">
			Категории
		</div>
		<div class="vertical-navigation">
			<ul>
				{classes}
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