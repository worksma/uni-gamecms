<style>
	.playground-tbody tr {
		background-color: #324965;
	}
	
	.right-col {
		display: flex;
		justify-content: flex-end;
	}
	
	.playground-tbody tr:hover {
		cursor:pointer;
		border: solid 2px #0000007d;
		background-color: #324965;
	}
	
	.playground-tbody img {
		width: 64px;
		height: 64px;
		border: solid 2px white;
		background-color: #3A3A3A;
		margin-right: 0.2rem;
	}
	
	.playground-tbody td {
		color: white !important;
	}
</style>

<div class="col-lg-9 order-is-first">
	<div class="row">
		<div class="col-md-6">
			У Вас на счету {balance}<br>
			Курс 1 поинт {course} руб.
		</div>
		<div class="col-md-6 col-md-push-12 text-right">
			<a class="btn btn-default" href="/inventory">Мой инвентарь</a>
			<button class="btn btn-default" data-toggle="modal" data-target="#sellModalItems" onclick="sell_load_items();">Продать предмет</button>
		</div>
	</div>

	
	<div class="block">
		<div id="result_playground_buy"></div>
		<table class="table">
			<thead>
				<tr>
					<td>НАЗВАНИЕ</td>
					<td class="text-center">КОЛ-ВО</td>
					<td class="text-center">ЦЕНА</td>
				</tr>
			</thead>
			
			<tbody class="playground-tbody" id="product_sell"></tbody>
		</table>
	</div>
</div>

<div class="col-lg-3 order-is-last">
	<input type="hidden" id="search_category" value="{category}">
	<div class="block">
		<div class="block_head">Каталог товаров</div>
		<div class="vertical-navigation">
			<ul id="category">
				<center>Категорий нет</center>
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
<script>
	document.addEventListener("DOMContentLoaded", load_category);
	document.addEventListener("DOMContentLoaded", load_product_sell);
</script>
<link href="{site_host}files/toasts/toasty.min.css?v={cache}" rel="stylesheet">
<script src="{site_host}files/toasts/toasty.min.js?v={cache}" type="text/javascript"></script>

<!--[ Модальное окно продажи предметов ]!-->
<div class="modal fade" id="sellModalItems" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Продажа предметов</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<style>
					.card-img-top {
						border-radius: 0px;
					}
					
					.card:hover {
						cursor:pointer;
						border: solid 2px #0000007d;
						background-color: #324965;
						color:white;
					}
				</style>
				
				<div class="row" id="items"></div>
			</div>
		</div>
	</div>
</div>
<!--[ Модальное окно продажи предметов ]!-->