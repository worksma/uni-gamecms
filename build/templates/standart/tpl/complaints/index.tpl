<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">Жалобы</div>
		<div class="table-responsive mb-0">
			<table class="table table-bordered">
				<thead>
					<tr>
						<td>Действие</td>
						<td>Нарушитель</td>
						<td>Статус</td>
						<td>Дата создания</td>
					</tr>
				</thead>
				<tbody id="bans">
					{func Complaints:getList("{accusedProfileId}","{server}","{start}","{limit}")}
				</tbody>
			</table>
		</div>
	</div>

	<div id="pagination2">{pagination}</div>
</div>

<div class="col-lg-3 order-is-last">
	<div class="block">
		<a href="../complaints/add" class="btn btn-outline-primary btn-xl">Добавить жалобу</a>
	</div>

	{if('{servers}' != '')}
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
	{/if}

	{if(is_auth())}
		{include file="/home/navigation.tpl"}
	{else}
		{include file="/index/authorization.tpl"}
	{/if}
</div>