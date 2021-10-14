<div class="table-responsive">
	<table class="table table-bordered admin-settings">
		<tbody>
			<tr id="active{id}" class="{class}">
				<td>Активность</td>
				<td>
					{if('{active}' == '2')}
						<button id="on_{id}" onclick="start_srote({id});" class="btn btn-outline-primary" type="button">
							Разблокировать - {price}{{$messages['RUB']}}
						</button>
					{else}
						{if('{pause}' != '0')}
							<font class="text-warning">Приостановлен</font>
						{else}
							<font class="text-success">Активен</font>
						{/if}
					{/if}
					<small>Активность</small>
				</td>
			</tr>
			<tr>
				<td>Тип привязки</td>
				<td>
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<button {if('{active}' != '2')}onclick="edit_store({id}, 'type');"{/if} class="btn btn-outline-primary" type="button" {disabled}>Изменить</button>
						</div>
						<select class="form-control" id="store_type_{id}" onchange="local_change_admin_type({id});" {disabled}>
							{if('{peg_1}' != '2')}<option {if('{type}' == 'a')} selected {/if} value="1">Ник + пароль</option>{/if}
							{if('{peg_2}' != '2')}<option {if('{type}' == 'ce')} selected {/if} value="2">STEAM ID</option>{/if}
							{if('{peg_3}' != '2')}<option {if('{type}' == 'ca')} selected {/if} value="3">STEAM ID + пароль</option>{/if}
						</select>
					</div>
					<small>Тип привязки</small>
				</td>
			</tr>
			<tr id="input_name{id}">
				<td>Идентификатор</td>
				<td>
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<button {if('{active}' != '2')}onclick="edit_store({id}, 'name');"{/if} class="btn btn-outline-primary" type="button" {disabled}>Изменить</button>
						</div>
						<input id="player_name_{id}" type="text" maxlength="32" value="{name}" class="form-control" {disabled}>
					</div>
					<small>Идентификатор</small>
				</td>
			</tr>
			<tr id="input_pass{id}">
				<td>Пароль</td>
				<td>
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<button {if('{active}' != '2')}onclick="edit_store({id}, 'pass');"{/if} class="btn btn-outline-primary" type="button" {disabled}>Изменить</button>
						</div>
						<input id="player_pass_{id}" type="text" maxlength="32" value="{pass}" class="form-control" {disabled}>
					</div>
					<small>Пароль</small>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<script>local_change_admin_type({id});</script>

<h4 class="modal-title">
	Услуги
</h4>
<div class="table-responsive mb-0 mt-3">
	<table class="table table-bordered">
		<thead>
			<tr>
				<td>#</td>
				<td>Услуга</td>
				<td>Флаги/Группа</td>
				<td>Дата покупки</td>
				<td>Дата окончания</td>
				<td>Осталось</td>
			</tr>
		</thead>
		<tbody id="admins_services{id}">
			{services}
		</tbody>
	</table>
</div>