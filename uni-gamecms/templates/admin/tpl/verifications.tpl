<div class="page">
	<div class="row">
		<div class="col-md-12">
			<div class="block">
				<div class="block_head">Запросы на верификацию</div>
				<div class="table-responsive mb-0">
					<table class="table table-bordered admins">
						<thead>
							<tr>
								<td>Пользователь</td>
								<td>Дата регистрации</td>
								<td>Параметры</td>
							</tr>
						</thead>
						<tbody id="list_verifications">
							{if($very = new Verification($pdo))}
								<?echo $very->admin_request_verifications();?>
							{/if}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="/ajax/addons/verification/ajax-very-admin.js?v={cache}"></script>