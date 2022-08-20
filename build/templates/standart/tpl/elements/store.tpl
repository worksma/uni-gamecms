<tr id="admin{id}">
	<td>{i}</td>
	<td id="new_name_{id}">{name}</td>
	<td id="new_active_{id}">
		{if('{active}' == '2')}
			<font id="admin_block{id}" class="text-danger" data-container="body" data-toggle="popover" data-placement="top" title="Заблокирован" data-content='Причина: {cause}<br>Цена разблокировки: {price}{{sys()->currency()->lang}}{if('{link}'!='')}<br>Ссылка на <a target="blank" href="{link}">доказательства</a>{/if}'>
				Заблокирован
			</font>
			<script>$('#admin_block{id}').popover({ html: true, animation: true, trigger: "click", delay: { "show": 100, "hide": 100 } });</script>
		{else}
			{if('{pause}' != '0')}
				<font class="text-warning">Приостановлен</font>
			{else}
				<font class="text-success">Активен</font>
			{/if}
		{/if}
	</td>
	<td>{server}</td>
	<td>
		<button class="btn btn-default btn-sm" onclick="get_stores_info({id});" data-target="#modal{id}" data-toggle="modal">Настройка</button>
	</td>
</tr>
<tr class="hidden-tr">
	<td>
		<div id="modal{id}" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title">Подробная информация</h4>
					</div>
					<div class="modal-body" id="store_info{id}">
						<div class="loader"></div>
					</div>
				</div>
			</div>
		</div>
	</td>
</tr>