<tr>
	<td>{i}</td>
	<td>
		{if('{server}' == '')}
			{if('{user_id}' == '0')}
			<a href="#">
				<img src="../files/avatars/no_avatar.jpg" alt="Неизвестно"> Неизвестно
			</a>
			{else}
				<a target="_blank" href="../profile?id={user_id}" title="{gp_name}">
					<img src="../{avatar}" alt="{login}"> <span style="color: {gp_color}">{login}</span>
				</a>
			{/if}
		{else}
			{server}
		{/if}
	</td>
	<td>
		{name}
		{if('{active}' == '2')}
			<a id="admin{id}" class="text-danger" data-container="body" data-toggle="popover" data-placement="top" title="Приостановлен" data-content='Причина: {cause}<br>Цена разблокировки: {price}{{sys()->currency()->lang}}{if('{link}'!='')}<br>Ссылка на <a target="blank" href="{link}">доказательства</a>{/if}'>
				(приостановлен)
			</a>
			<script>$('#admin{id}').popover({ html: true, animation: true, trigger: "click", delay: { "show": 100, "hide": 100 } });</script>
		{/if}
	</td>
	<td>
		<a href="#" onclick="get_admin_info2({id});" data-target="#modal{id}" data-toggle="modal" title="Подробнее">{services}</a>
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
						<h4 class="modal-title">Услуги</h4>
					</div>
					<div class="modal-body">
						<div class="table-responsive mb-0">
							<table class="table table-bordered">
								<thead>
									<tr>
										<td>#</td>
										<td>Услуга</td>
										<td>Дата покупки</td>
										<td>Дата окончания</td>
										<td>Осталось</td>
									</tr>
								</thead>
								<tbody id="admin_info{id}">
									<tr>
										<td colspan="10">
											<div class="loader"></div>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</td>
</tr>