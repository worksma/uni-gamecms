<tr id="bid{bid}">
	<td width="1%">
		<button class="btn btn-default btn-sm" onclick="search_ban2({bid},{server});">
			Выбрать
		</button>
	</td>
	<td><a class="c-p" data-target="#ban{bid}" data-toggle="modal" title="Подробнее">{player_nick}</a></td>
	<td><a class="c-p" data-target="#ban{bid}" data-toggle="modal" title="Подробнее">{ban_reason}</a></td>
	<td><a class="c-p" data-target="#ban{bid}" data-toggle="modal" title="Подробнее">{time}</a></td>
</tr>
<tr>
	<td>
		<div id="ban{bid}" class="modal fade bs-example-modal-lg">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title">Подробная информация</h4>
					</div>
					<div class="modal-body" id="baninfo{bid}">
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
								<td><b> Дата окончания: </b></td>
								<td><p class="text-{color}">{time}</p></td>
							</tr>
							<tr>
								<td><b> Срок: </b></td>
								<td><p class="text-{color}">{ban_length}</p></td>
							</tr>
							<tr>
							<td><b> Сервер: </b></td>
								<td>{server_name} - {address}</td>
							</tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</td>
</tr>