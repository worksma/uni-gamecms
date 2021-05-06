<tr id="admin{id}" {if('{comment}' != '')} title="{comment}" {/if} class="{if('{active}' == '2')} danger {/if} {if('{pause}' != '0')} warning {/if}">
	<td>
		{i}
		<div id="admin_modal{id}" class="modal fade bs-example-modal-lg">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Подробная информация</h4>
					</div>
					<div class="modal-body" id="admin_info{id}">

					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
					</div>
				</div>
			</div>
		</div>
	</td>
	<td>
		<a class="btn btn-default btn-xs w-100" onclick="get_admin_info({id});" data-target="#admin_modal{id}" data-toggle="modal" title="Подробнее">Опции</a>
	</td>
	<td id="new_user_{id}">
		{if('{user_id}' == '0')}
		<a>
			<img src="../files/avatars/no_avatar.jpg" alt="Укажите id пользователя"> Неизвестно &nbsp
		</a>
		{else}
		<a target="_blank" href="../profile?id={user_id}">
			<img src="../{avatar}" alt="{login}"> {login}
		</a>
		{/if}
	</td>
	<td id="new_name_{id}">
		{name}
		{if('{comment}' != '')}
			<span class="glyphicon glyphicon-tag"></span>
		{/if}
	</td>
	<td id="new_services_{id}">
		{services}
	</td>
	<td id="new_services_{id}">
		{ending_date}
	</td>
</tr>