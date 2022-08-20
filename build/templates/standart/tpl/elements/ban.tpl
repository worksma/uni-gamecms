<tr class="{class}" id="bid{bid}" data-target="#ban{bid}" data-toggle="modal" title="Подробнее">
	<td>{player_nick}</td>
	<td>{ban_reason}</td>
	<td>
		<span id="ban_length{bid}">{ban_length}</span>
		{if('{price}' != '0')}
		<a href="#" class="{disp}" id="buy_unban_btn{bid}" title="Купить разбан за {price} {{sys()->currency()->lang}}">
			(Разбан {price}{{sys()->currency()->lang}})
		</a>
		{/if}
	</td>
	<td id="ban_end{bid}">{time}</td>
	<td>{admin_nick}</td>
</tr>
<tr class="hidden-tr">
	<td>
		<div id="ban{bid}" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title">Подробная информация</h4>
					</div>
					<div class="modal-body" id="baninfo{bid}">
						<div class="table-responsive mb-0">
							<table class="table table-bordered">
								<tr>
									<td><b> Ban id: </b></td>
									<td>{bid}</td>
								</tr>
								<tr>
									<td><b> Ip забаненного: </b></td>
									<td>{player_ip}</td>
								</tr>
								<tr>
									<td><b> Steam id забаненного: </b></td>
									<td>{player_id}</td>
								</tr>
								<tr>
									<td><b> Ник забаненного: </b></td>
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
											<input onclick="$('.ui-datepicker-current').attr('onclick', 'set_admin_date_forever({bid}, \'#ban_end_input{bid}\')');$('.ui-datepicker-current2').attr('onclick', 'change_ban_end({bid}, {server});');" class="form-control" type="text" id="ban_end_input{bid}" value="{ban_end}">
											<script>
											$('#ban_end_input{bid}').datetimepicker({
												timeInput: true,
												timeFormat: "HH:mm",
												onSelect: function() {
													setTimeout(function() {
														$('.ui-datepicker-current').attr('onclick', 'set_admin_date_forever({bid}, \'#ban_end_input{bid}\')');
														$('.ui-datepicker-current2').attr('onclick', 'change_ban_end({bid}, {server});');
													}, 500);
												}
											});
											</script>
										{else}
											<p class="text-{class}" id="ban_end_full{bid}">{time}</p>
										{/if}
									</td>
								</tr>
								<tr>
									<td><b> Срок: </b></td>
									<td>
										<p class="text-{class}" id="ban_length_full{bid}">{ban_length}</p>
									</td>
								</tr>
								<tr>
									<td><b> Ip сервера: </b></td>
									<td>{address}</td>
								</tr>
								<tr id="unban_btns{bid}" class="{disp}">
									<td><b> Разбан: </b></td>
									<td>
										{if('{price}' != '0')}
										<button class="btn btn-outline-primary" onclick="buy_unban({bid},{server});" title="Купить разбан за {price} {{sys()->currency()->lang}}">
											Купить разбан - {price} {{sys()->currency()->lang}}
										</button>
										{/if}
										<button class="btn btn-outline-primary {disp2}" onclick="close_ban2('{server}','{bid}');" id="unban_btn{bid}">
											Разбанить
										</button>
									</td>
								</tr>
								{if('{ban_closed}' != '')}
								<tr id="ban_closed{bid}">
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