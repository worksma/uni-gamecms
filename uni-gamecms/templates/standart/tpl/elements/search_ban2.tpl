<div class="panel panel-default mt-10">
	<div class="panel-body pd-0">
		<div class="table-responsive">
			<table class="table table-bordered br-3 mb-0 baninfo">
				<tr>
					<td>Ban id: </td>
					<td>
						{bid}
						<input type="hidden" class="form-control" id="bid_db" maxlength="11" value="{bid}">
					</td>
				</tr>
				<tr>
					<td>Ip забаненного: </td>
					<td>{player_ip}</td>
				</tr>
				<tr>
					<td>Steam id забаненного: </td>
					<td>{player_id}</td>
				</tr>
				<tr>
					<td>Ник забаненного: </td>
					<td>
						{player_nick}
						<input type="hidden" class="form-control" id="nick_db" maxlength="250" value="{player_nick}">
					</td>
				</tr>
				<tr>
					<td>Причина: </td>
					<td>
						{ban_reason}
						<input type="hidden" class="form-control" id="reason_db" maxlength="11" value="{ban_reason}">
					</td>
				</tr>
				<tr>
					<td>Дата окончания: </b></td>
					<td><p class="text-{color}">{time}</p></td>
				</tr>
			</table>
		</div>
	</div>
</div>
<script>$('#dop').attr('class', 'disp-b');</script>