<tr id="admin{id}" {if('{comment}' != '')} title="{comment}" {/if} class="{if('{active}' == '2')} danger {/if} {if('{pause}' != '0')} warning {/if}">
	<td>
		{i}
		<div id="admin_modal{id}" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title">Подробная информация</h4>
					</div>
					<div class="modal-body" id="admin_info{id}">
						<div class="loader"></div>
					</div>
				</div>
			</div>
		</div>
	</td>
	<td>
		<a class="btn btn-default btn-sm w-100" onclick="get_admin_info({id});" data-target="#admin_modal{id}" data-toggle="modal" title="Подробнее">Опции</a>
	</td>
	<td id="new_user_{id}">
		{if('{user_id}' == '0')}
		<a>
			<img src="../files/avatars/no_avatar.jpg" alt="Укажите id пользователя"> Неизвестно
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
			<i class="fas fa-bookmark"></i>
		{/if}
	</td>
	<td id="new_services_{id}">
		{services}
	</td>
	<td id="new_services_{id}">
		{ending_date}
	</td>
</tr>