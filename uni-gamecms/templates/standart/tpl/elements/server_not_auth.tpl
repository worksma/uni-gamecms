<div class="server">
	<h3>{name}</h3>

	<div class="map-image" style="background: url({map_img});"></div>

	<p>Карта: {map_name}</p>
	<p onclick="get_players({id});" data-toggle="modal" data-target="#server{id}">Игроков: {now}/{max}</p>
	<p><a href="steam://connect/{address}" title="Подключиться к серверу">{address}</a></p>

	<div class="modal fade" id="server{id}">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Игроки</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="table-responsive mb-0">
						<table class="table table-bordered">
							<thead>
								<tr>
									<td>#</td>
									<td>Ник</td>
									<td>Убийств</td>
									<td>Время</td>
								</tr>
							</thead>
							<tbody id="players{id}">
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
</div>