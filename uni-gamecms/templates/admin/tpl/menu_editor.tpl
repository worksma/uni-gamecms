<div class="page">
	<div class="block">
		<div class="block_head">
			Добавить пункт
		</div> 
		<div class="col-md-5 pd-0">
			<div class="form-group">
				<label for="input_name">
					<h4>
						Название
					</h4>
				</label>
				<input type="text" class="form-control" id="input_name" maxlength="255" autocomplete="off">
			</div>
		</div>
		<div class="col-md-5 pd-0">
			<div class="form-group">
				<label for="input_link">
					<h4>
						Ссылка
					</h4>
				</label>
				<input type="text" class="form-control" id="input_link" maxlength="255" autocomplete="off">
			</div>
		</div>
		<div class="col-md-2 pd-0">
			<div class="form-group">
				<label for="checbox">
					<h4>
						Доступ
					</h4>
				</label>
				<select class="form-control" id="checbox">
					<option value="1">Для всех</option>
					<option value="2">Для авторизованных</option>
					<option value="3">Для неавторизованных</option>
				</select>
			</div>
		</div>

		<button onclick="create_menu();" type="button" class="btn btn-default mt-10">Создать</button><br>
	</div>
	<div class="block">
		<div class="block_head">
			 Добавить раздвижной пункт
		</div> 
		<div class="col-md-6 pd-0">
			<div class="form-group">
				<label for="input_sliding_name">
					<h4>
						Название
					</h4>
				</label>
				<input type="text" class="form-control" id="input_sliding_name" maxlength="255" autocomplete="off">
			</div>
		</div>
		<div class="col-md-6 pd-0">
			<div class="form-group">
				<label for="sliding_checbox">
					<h4>
						Доступ
					</h4>
				</label>
				<select class="form-control" id="sliding_checbox">
					<option value="1">Для всех</option>
					<option value="2">Для авторизованных</option>
					<option value="3">Для неавторизованных</option>
				</select>
			</div>
		</div>

		<button onclick="create_sliding_menu();" type="button" class="btn btn-default mt-10">Создать</button><br>
	</div>
	<div class="block">
		<div class="block_head">
			Список
		</div>
		<div class="content">
			<div class="table-responsive mb-0">
				<table class="table table-bordered">
					<thead>
						<tr>
							<td width="5%">#</td>
							<td>Название/Ссылка</td>
							<td width="20%">Доступ</td>
							<td width="15%">Порядок</td>
							<td width="15%">Действие</td>
						</tr>
					</thead>
					<tbody id="menu">
						<tr><td colspan="7"><center><img src="{site_host}templates/admin/img/loader.gif"></center></td></tr>
					</tbody>
				</table>
			</div>
		</div>
		<script>load_menu();</script>
	</div> 
</div>
