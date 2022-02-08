<div class="page">
	<div class="row">
		<div class="col-md-12">
			<div class="block mb-4">
				<div class="block_head">Подтвержденные профили</div>
				<div class="table-responsive mb-0">
					<table class="table table-bordered admins">
						<thead>
							<tr>
								<td>#</td>
								<td>Пользователь</td>
								<td>Параметры</td>
							</tr>
						</thead>
						<tbody>
							<?
								$sth = pdo()->query("SELECT * FROM `users` WHERE `verification`='1'");

								if(!$sth->rowCount()):
							?>
									<tr>
										<td colspan="3">Пользователей нет</td>
									</tr>
							<?
							else:
								while($row = $sth->fetch(PDO::FETCH_OBJ)):
							?>
									<tr>
										<td><?=$row->id;?></td>
										<td>
											<?=("<a href=\"/profile?id={$row->id}\" target=\"_blank\">" . $row->login . "</a>");?>
										</td>
										<td onclick="off_very(<?=$row->id;?>);" style="cursor:pointer;">
											Забрать
										</td>
									</tr>
								<?
								endwhile;
								endif;
							?>
						</tbody>
					</table>
				</div>
			</div>

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