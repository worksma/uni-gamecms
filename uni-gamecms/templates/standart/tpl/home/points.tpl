<div id="points_result"></div>

<style>
	.category-point {
		position:absolute;
		top:10px;
		left:10px;
	}
	
	.card-to-points {
		padding:10px;
	}
</style>

<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">Магазин за бонусы</div>
		
		<div class="row">
			{product_points_list}
		</div>
		
		<div class="text-muted" style="text-align:right;">
			У вас в наличии: {points} бонусов
		</div>
	</div>
</div>

<div class="col-lg-3 order-is-last">
	<div class="block">
		<div class="block_head">Каталог товаров</div>
		<div class="vertical-navigation">
			<ul>
				{category_points_list}
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
<link href="{site_host}templates/admin/js/toasts/toasty.min.css" rel="stylesheet"/>
<script src="{site_host}templates/admin/js/toasts/toasty.min.js" type="text/javascript"></script>