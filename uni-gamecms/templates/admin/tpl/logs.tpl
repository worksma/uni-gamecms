<div class="page">
	<div class="col-md-4">
		<div class="block">
			<div class="block_head">
				Общие логи
			</div>
			<div id="logs"><script>load_logs();</script></div>
		</div>
	</div>
	<div id="1" class="modal fade">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						Общие логи
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
					</h4>
				</div>
				<div class="modal-body">
					{file1}
				</div>
				<div class="modal-footer">
					<button class="btn btn-default" data-dismiss="modal" type="button">
						Закрыть
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="block">
			<div class="block_head">
				Общие логи с ошибками
			</div>
			<div id="error_logs"><script>load_error_logs();</script></div>
		</div>
	</div>
	<div id="2" class="modal fade">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						Общие логи с ошибками
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
					</h4>
				</div>
				<div class="modal-body">
					{file2}
				</div>
				<div class="modal-footer">
					<button class="btn btn-default" data-dismiss="modal" type="button">
						Закрыть
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="block">
			<div class="block_head">
				Логи с ошибками базы данных
			</div>
			<div id="pdo_errors"><script>load_pdo_errors();</script></div>
		</div>
	</div>
	<div id="3" class="modal fade">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						Логи с ошибками базы данных
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
					</h4>
				</div>
				<div class="modal-body">
					{file3}
				</div>
				<div class="modal-footer">
					<button class="btn btn-default" data-dismiss="modal" type="button">
						Закрыть
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="clearfix"></div>

	<div class="col-md-4">
		<div class="block">
			<div class="block_head">
				Лог успешных пополнений счета
			</div>
			<div id="payment_successes"><script>load_payment_successes();</script></div>
		</div>
	</div>
	<div id="4" class="modal fade">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						Лог успешных пополнений счета
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
					</h4>
				</div>
				<div class="modal-body">
					{file4}
				</div>
				<div class="modal-footer">
					<button class="btn btn-default" data-dismiss="modal" type="button">
						Закрыть
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="block">
			<div class="block_head">
				Лог ошибок при пополнений счета
			</div>
			<div id="payment_errors"><script>load_payment_errors();</script></div>
		</div>
	</div>
	<div id="5" class="modal fade">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						Лог ошибок при пополнений счета
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
					</h4>
				</div>
				<div class="modal-body">
					{file5}
				</div>
				<div class="modal-footer">
					<button class="btn btn-default" data-dismiss="modal" type="button">
						Закрыть
					</button>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="block">
			<div class="block_head">
				Лог операция с привилегиями
			</div>
			<div id="services_log"><script>load_services_log();</script></div>
		</div>
	</div>
	<div id="6" class="modal fade">
		<div class="modal-dialog modal-lg2">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						Лог операция с привилегиями
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
					</h4>
				</div>
				<div class="modal-body">
					{file6}
				</div>
				<div class="modal-footer">
					<button class="btn btn-default" data-dismiss="modal" type="button">
						Закрыть
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-12">
		<div class="block">
			<div class="block_head">
				Заблокированные ip
			</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="add_banned_ip();">Добавить</button>
					</span>
					<input type="text" class="form-control" id="ip" maxlength="20" autocomplete="off" placeholder="Добавить вечный бан по ip + cookies (укажите ip)">
				</div>
				<div class="table-responsive mb-0">
					<table class="bans_table table table-bordered">
						<thead>
							<tr>
								<td>IP</td>
								<td>Истекает</td>
								<td>Действие</td>
							</tr>
						</thead>
						<tbody id="banned_ip">
							<script>load_banned_ip();</script>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>