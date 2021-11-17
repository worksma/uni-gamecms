{include file="config.tpl"}
<?
	$ServerCommands = new ServerCommands();
	$commands = $ServerCommands->getCommands('{id}');
?>
{if($monitoringType == 0)}
	<div class="server">
		<h3>
			{if('{rcon}' == '1' && count($commands) > 0 && is_auth() && is_worthy_specifically("v", {id}))}
				<span data-toggle="modal" data-target="#server-management-modal{id}">
					⚙️
				</span>

				<div class="modal fade" id="server-management-modal{id}">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title">Управление сервером</h4>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<div class="input-group input-group-sm">
									<div class="input-group-prepend">
										<button
												class="btn btn-outline-primary"
												type="button"
												onclick='doRconCommandOnServer(
														$("#server-management-command-id{id}").val(),
														$("#server-management-command-id{id} option:selected").attr("data-command-params"),
														{id}
												);'
										>
											Выполнить
										</button>
									</div>
									<select id="server-management-command-id{id}" class="form-control">
										{for($l = 0; $l < count($commands); $l++)}
											<option value="{{$commands[$l]->id}}" data-command-params='{{$commands[$l]->params}}'>
												{{$commands[$l]->title}}
											</option>
										{/for}
									</select>
								</div>

								<pre class="mt-3" id="server-management-command-sending-result{id}" style="display: none"></pre>
							</div>
						</div>
					</div>
				</div>
			{/if}
			️
			{name}
		</h3>

		<div class="map-image" style="background: url({map_img});"></div>

		<p>Карта: {map_name}</p>
		<p onclick="get_players({id});" data-toggle="modal" data-target="#server-players-modal{id}">Игроков: {now}/{max}</p>
		<p><a href="steam://connect/{address}" title="Подключиться к серверу">{address}</a></p>

		<div class="modal fade" id="server-players-modal{id}">
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
										{if('{rcon}' == '1' && is_auth() && is_worthy_specifically("s", {id}))}
											<td>Действие</td>
										{/if}
									</tr>
								</thead>
								<tbody id="server-players{id}">
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
{else}
	<tr {if({i} > $countOfServersDisplayed && $countOfServersDisplayed != 0)}style="display: none;"{/if}>
		<td>
			{if({i} > $countOfServersDisplayed && $countOfServersDisplayed != 0)}
			<script>
				if(!$("tr").is("#show_all_servers")) {
					$("#servers").append("<tr style='cursor: pointer;' onclick=\"$('#servers tr').each(function(){$(this).fadeIn();});$('#show_all_servers').fadeOut();\" id='show_all_servers'><td colspan='10'>Отобразить все сервера</td></tr>");
				}
			</script>
			{/if}

			{if('{rcon}' == '1' && count($commands) > 0 && is_auth() && is_worthy_specifically("v", {id}))}
				<span data-toggle="modal" data-target="#server-management-modal{id}">
					⚙️
				</span>

				<div class="modal fade" id="server-management-modal{id}">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<h4 class="modal-title">Управление сервером</h4>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<div class="input-group input-group-sm">
									<div class="input-group-prepend">
										<button
												class="btn btn-outline-primary"
												type="button"
												onclick='doRconCommandOnServer(
														$("#server-management-command-id{id}").val(),
														$("#server-management-command-id{id} option:selected").attr("data-command-params"),
												{id}
														);'
										>
											Выполнить
										</button>
									</div>
									<select id="server-management-command-id{id}" class="form-control">
										{for($l = 0; $l < count($commands); $l++)}
											<option value="{{$commands[$l]->id}}" data-command-params='{{$commands[$l]->params}}'>
												{{$commands[$l]->title}}
											</option>
										{/for}
									</select>
								</div>

								<pre class="mt-3" id="server-management-command-sending-result{id}" style="display: none"></pre>
							</div>
						</div>
					</div>
				</div>
			{/if}

			<p>
				{if('{game}' == 'Counter-Strike: Global Offensive')}
					<img title="Counter-Strike: Global Offensive" tooltip="yes" class="game-icon" src="../templates/{template}/img/csgo.png">
				{/if}
				{if('{game}' == 'Counter-Strike: Source')}
					<img title="Counter-Strike: Source" tooltip="yes" class="game-icon" src="../templates/{template}/img/css.png">
				{/if}
				{if('{game}' == 'Counter-Strike: 1.6')}
					<img title="Counter-Strike: 1.6" tooltip="yes" class="game-icon" src="../templates/{template}/img/cs16.png">
				{/if}

				{name}
			</p>
		</td>
		<td>
			<p id="map{id}" class="td" data-container="body" data-toggle="popover" data-placement="top" data-content='<img class="popover-map-img" src="{map_img}">'>
				{map_name}
				<script>$('#map{id}').popover({ html: true, animation: true, trigger: "hover" });</script>
			</p>
		</td>
		<td>
			<div class="progress servers-online-line" onclick="get_players({id});" data-toggle="modal" data-target="#server{id}" >
				<div class="progress-val">{now}/{max}</div>
				<div class="progress-bar bg-{color}" role="progressbar" style="width: {percentage}%;" aria-valuenow="{percentage}" aria-valuemin="0" aria-valuemax="100"></div>
			</div>

			<div class="modal fade" id="server{id}">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title">Игроки</h4>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">×</span>
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
										{if('{rcon}' == '1' && isset($_SESSION['id']) && is_worthy_specifically("s", {id}))}
											<td>Действие</td>
										{/if}
									</tr>
									</thead>
									<tbody id="server-players{id}">
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
		<td><a href="steam://connect/{address}">{address}</a></td>
		<td>
			<a targt="_blank" href="../admins?server={id}"><i class="fas fa-users" title="Администраторы"></i></a>
			<a targt="_blank" href="../banlist?server={id}"><i class="fas fa-ban" title="Забаненые"></i></a>
			<a targt="_blank" href="../bans/index?server={id}"><i class="fas fa-calendar-check" title="Заявки на разбан"></i></a>
		</td>
	</tr>
{/if}