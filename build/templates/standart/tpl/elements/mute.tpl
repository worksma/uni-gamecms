<tr class="{class}" id="bid{bid}" data-target="#modal{bid}" data-toggle="modal" title="Подробнее">
	<td>{player_nick}</td>
	<td>{ban_reason}</td>
	<td>
		{if('{type}' == '0' || '{type}' == '3')}
			Mute+Gag 
		{else}
			{if('{type}' == '1')}
				Mute
			{else}
				Gag
			{/if}
		{/if}
	</td>
	<td>
		<span id="mute_length{bid}">{ban_length}</span>
		{if('{price}' != '0')}
		<a class="{disp}" id="buy_unmute_btn{bid}" title="Купить размут за {price} {{sys()->currency()->lang}}">
			(Размут {price} {{sys()->currency()->lang}})
		</a>
		{/if}
	</td>
	<td id="mute_end{bid}">{time}</td>
	<td>{admin_nick}</td>
</tr>
<tr class="hidden-tr">
	<td>
		<div id="modal{bid}" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title">Подробная информация</h4>
					</div>
					<div class="modal-body" id="muteinfo{bid}">
						<div class="table-responsive mb-0">
							<table class="table table-bordered">
								<tr>
									<td><b> Mute id: </b></td>
									<td>{bid}</td>
								</tr>
								<tr>
									<td><b> Steam id заблокированного: </b></td>
									<td>{player_id}</td>
								</tr>
								<tr>
									<td><b> Ник заблокированного: </b></td>
									<td>{player_nick}</td>
								</tr>
								<tr>
									<td><b> Ник админа: </b></td>
									<td>{admin_nick}</td>
								</tr>
								<tr>
									<td><b> Причина: </b></td>
									<td>{ban_reason}</td>
								</tr>
								<tr>
									<td><b> Дата бана: </b></td>
									<td><p>{ban_created}</p></td>
								</tr>
								<tr>
									<td><b> Дата окончания: </b></td>
									<td>
										{if(is_worthy_specifically("s", {server}))}
											<input onclick="$('.ui-datepicker-current').attr('onclick', 'set_admin_date_forever({bid}, \'#mute_end_input{bid}\')');$('.ui-datepicker-current2').attr('onclick', 'change_mute_end({bid}, {server});');" class="form-control input-xs c-p" type="text" id="mute_end_input{bid}" value="{ban_end}">
											<script>
											$('#mute_end_input{bid}').datetimepicker({
												timeInput: true,
												timeFormat: "HH:mm",
												onSelect: function() {
													setTimeout(function() {
														$('.ui-datepicker-current').attr('onclick', 'set_admin_date_forever({bid}, \'#mute_end_input{bid}\')');
														$('.ui-datepicker-current2').attr('onclick', 'change_mute_end({bid}, {server});');
													}, 500);
												}
											});
											</script>
										{else}
											<p class="text-{class}" id="mute_end_full{bid}">{time}</p>
										{/if}
									</td>
								</tr>
								<tr>
									<td><b> Срок: </b></td>
									<td><p class="text-{class}" id="mute_length_full{bid}">{ban_length}</p></td>
								</tr>
								<tr>
									<td><b> Ip сервера: </b></td>
									<td>{address}</td>
								</tr>
								<tr class="{disp}" id="unmute_btns{bid}">
									<td><b> Размут: </b></td>
									<td>
										{if('{price}' != '0')}
										<button class="btn btn-outline-primary" onclick="buy_unmute({bid},{server});" title="Купить размут за {price} {{sys()->currency()->lang}}">
											Купить размут - {price} {{sys()->currency()->lang}}
										</button>
										{/if}
										<button class="btn btn-outline-primary {disp2}" onclick="close_mute('{server}','{bid}');" id="unmute_btn{bid}">
											Разблокировать
										</button>
									</td>
								</tr>
								{if('{ban_closed}' != '')}
								<tr id="mute_closed{bid}">
									<td><b> Блокировку снял: </b></td>
									<td>
										{ban_closed}
									</td>
								</tr>
								{/if}
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</td>
</tr>