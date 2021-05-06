<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">Заявки на разбан</div>
		<div class="table-responsive mb-0">
			<table class="table table-bordered">
				<thead>
					<tr>
						<td>Ник</td>
						<td>Пользователь</td>
						<td>Статус</td>
						<td>Сервер</td>
						<td>Дата</td>
					</tr>
				</thead>
				<tbody id="bans">
					{func GetData:bans_applications("{start}","{server}","{limit}")}
				</tbody>
			</table>
		</div>
	</div>
	<div id="pagination2">{pagination}</div>
</div>

<div class="col-lg-3 order-is-last">
	<div class="block">
		<a href="../bans/add_ban" class="btn btn-outline-primary btn-xl">Добавить заявку</a>
	</div>
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
	{else}
		{include file="/index/authorization.tpl"}
	{/if}
</div>