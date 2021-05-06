<tr id="{id}">
	<td>{j}</td>
	<td>{name}</td>
	<td>
		<div class="input-group">
			{if('{rights}' != '')}
				<span class="input-group-btn">
					<button title="Изменить" class="btn btn-default btn-xs" type="button" {if('{active}' == '2')} disabled {else} onclick="change_admin_flags({id}, {admin_id}, null);" {/if}>
						<span class="glyphicon glyphicon-pencil"></span>
					</button>
					<button title="Установить стандартные флаги услуги" class="btn btn-default btn-xs" type="button" {if('{active}' == '2')} disabled {else} onclick="change_admin_flags({id}, {admin_id}, 'none');" {/if}>
						<span class="glyphicon glyphicon-refresh"></span>
					</button>
				</span>
				<input id="service_flags{id}" value="{rights}" type="text" class="form-control input-sm" placeholder="Флаги">
			{/if}
			{groups}
		</div>
	</td>
	{if('{server_type}' == '4')}
	<td>{immunity}</td>
	{/if}
	<td>{if('{bought_date}' != '0000-00-00 00:00:00')} {bought_date_full} {else} Неизвестно {/if}</td>
	<td>
		<input onclick="$('.ui-datepicker-current').attr('onclick', 'set_admin_date_forever({id})');$('.ui-datepicker-current2').attr('onclick', 'change_admin_days({id}, {admin_id});');" class="form-control f-l" type="text" id="date_end{id}" value="{ending_date}" {if('{active}' == '2' || '{pause}' != '0')} disabled {/if}>
		<script>
		$('#date_end{id}').datetimepicker({
			timeInput: true,
			timeFormat: "HH:mm",
			onSelect: function() {
				setTimeout(function() {
					$('.ui-datepicker-current').attr('onclick', 'set_admin_date_forever({id})');
					$('.ui-datepicker-current2').attr('onclick', 'change_admin_days({id}, {admin_id});');
				}, 500);
			}
		});
		</script>
	</td>
	<td><p class="text-{color}">{left}</p></td>
	<td>
		<button class="btn btn-default btn-xs" {if('{active}' == '2')} disabled {else} onclick="dell_admin_service({id});" {/if}>Удалить</button>
	</td>
</tr>