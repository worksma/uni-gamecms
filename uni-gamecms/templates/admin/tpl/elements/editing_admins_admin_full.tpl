<div class="row">
	<div class="col-md-6">
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr>
						<td>ID Пользователя</td>
						<td>
							<div class="input-group">
								<span class="input-group-btn">
									<button {if('{active}' != '2')}onclick="edit_admin({id}, 'user_id');"{/if} class="btn btn-default" type="button" {if('{active}' == '2')} disabled {/if}>Изменить</button>
								</span>
								<span class="input-group-btn w-100">
									<input id="player_user_id_{id}" maxlength="5" type="number" value="{user_id}" class="form-control w-100" {if('{active}' == '2')} disabled {/if}>
								</span>
							</div>
						</td>
					</tr>
					<tr>
						<td>Тип</td>
						<td>
							<div class="input-group">
								<span class="input-group-btn">
									<button {if('{active}' != '2')}onclick="edit_admin({id}, 'type');"{/if} class="btn btn-default" type="button" {if('{active}' == '2')} disabled {/if}>Изменить</button>
								</span>
								<span class="input-group-btn w-100">
									<select class="form-control" id="store_type_{id}" onchange="local_change_admin_type({id});" {if('{active}' == '2')} disabled {/if}>
										{if({binds_0})}
											<option value="1" {if('{type}' == 'a')} selected {/if}>Ник + пароль</option>
										{/if}
										{if({binds_1})}
											<option value="2" {if('{type}' == 'ce')} selected {/if}>STEAM ID</option>
										{/if}
										{if({binds_2})}
											<option value="3" {if('{type}' == 'ca')} selected {/if}>STEAM ID + пароль</option>
										{/if}
									</select>
								</span>
							</div>
						</td>
					</tr>
					<tr id="input_name{id}">
						<td>Ник/SteamID</td>
						<td>
							<div class="input-group">
								<span class="input-group-btn">
									<button {if('{active}' != '2')}onclick="edit_admin({id}, 'name');"{/if} class="btn btn-default" type="button" {if('{active}' == '2')} disabled {/if}>Изменить</button>
								</span>
								<span class="input-group-btn w-100">
									<input id="player_name_{id}" type="text" maxlength="32" value="{name}" class="form-control" {if('{active}' == '2')} disabled {/if}>
								</span>
							</div>
						</td>
					</tr>
					<tr id="input_pass{id}">
						<td>Пароль</td>
						<td>
							<div class="input-group">
								<span class="input-group-btn">
									<button {if('{active}' != '2')}onclick="edit_admin({id}, 'pass');"{/if} class="btn btn-default" type="button" {if('{active}' == '2')} disabled {/if}>Изменить</button>
								</span>
								<span class="input-group-btn w-100">
									<input id="player_pass_{id}" type="text" maxlength="32" value="{if(is_admin())}{pass}{/if}" class="form-control w-100" {if('{active}' == '2')} disabled {/if}>
								</span>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-md-6">
		<div class="table-responsive">
			<table class="table table-bordered">
				<tbody>
					<tr id="active{id}" class="{class}">
						<td>Активность</td>
						<td>
							<button id="stop_{id}" {if('{active}' != '0')}onclick="stop_adm({id});"{/if} class="btn btn-default {disp}" type="button" {if('{pause}' != '0')} disabled {/if}>
								Выключить администратора
							</button>
							<button id="on_{id}" {if('{active}' != '0')}onclick="start_adm({id});"{/if} class="btn btn-default {disp_2}" type="button" {if('{pause}' != '0')} disabled {/if}>
								Включить администратора
							</button>
						</td>
					</tr>
					<tr id="pause{id}" class="{class_2}">
						<td>Приостановка</td>
						<td>
							{if('{pause}' == '0')}
								<button {if('{active}' != '2')}onclick="pause_admin({id});"{/if} class="btn btn-default" type="button" {if('{active}' == '2')} disabled {/if}>
									Приостановить
								</button>
							{else}
								<button {if('{active}' != '2')}onclick="resume_admin({id});"{/if} class="btn btn-default" type="button" {if('{active}' == '2')} disabled {/if}>
									Запустить
								</button>
							{/if}
						</td>
					</tr>
					<tr>
						<td>Удалить</td>
						<td>
							<button {if('{active}' != '2')}onclick="dell_admin({id});"{/if} class="btn btn-default" type="button" {if('{active}' == '2')} disabled {/if}>
								Удалить администратора
							</button>
						</td>
					</tr>
					<tr>
						<td>Комментарий</td>
						<td>
							<div class="input-group">
								<span class="input-group-btn">
									<button {if('{active}' != '2')}onclick="edit_admin({id}, 'comment');"{/if} class="btn btn-default" type="button" {if('{active}' == '2')} disabled {/if}>Изменить</button>
								</span>
								<span class="input-group-btn w-100">
									<input id="player_comment_{id}" type="text" maxlength="1000" value="{comment}" class="form-control w-100" {if('{active}' == '2')} disabled {/if}>
								</span>
							</div>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>local_change_admin_type({id});</script>
<h4 class="modal-title f-l">Услуги</h4>
<button type="button" class="btn btn-default btn-sm f-r" data-dismiss="modal" {if('{active}' != '2')}onclick="add_service_to_admin({id});"{/if} {if('{active}' == '2')} disabled {/if}>Добавить услугу</button>
<div class="clearfix"></div>
<div class="table-responsive mb-0 mt-10">
	<table class="table table-bordered" id="server_admins">
		<thead>
			<tr>
				<td>#</td>
				<td>Услуга</td>
				<td>Флаги/Группа</td>
				{if('{server_type}' == '4')}
				<td>Иммунитет</td>
				{/if}
				<td>Дата покупки</td>
				<td>Дата окончания</td>
				<td>Осталось</td>
				<td>Действие</td>
			</tr>
		</thead>
		<tbody id="admins_services">
			{stores}
		</tbody>
	</table>
</div>