<div class="block" id="section_{id}">
	<div class="block_head">
		Настройка раздела
	</div>
	<form id="section_settings{id}">
		<div class="form-group">
			<label>
				<h4>Название</h4>
			</label>
			<input name="section_name" id="section_name{id}" type="text" class="form-control" value="{name}">
		</div>

		<div class="form-group">
			<label>
				<h4>Доступ</h4>
			</label>
			<div class="btn-group-toggle" data-toggle="buttons" id="access{id}">
				{groups}
			</div>
		</div>

		<button type="button" class="btn btn-primary btn-sm" onclick="edit_section('{id}');">
			Изменить
		</button>
		<button type="button" class="btn btn-primary btn-sm" onclick="dell_section('{id}');">
			Удалить
		</button>
		<button type="button" class="btn btn-primary btn-sm" onclick="up_section('{id}');">
			<span class="m-icon icon-up"></span>
		</button>
		<button type="button" class="btn btn-primary btn-sm" onclick="down_section('{id}');">
			<span class="m-icon icon-down"></span>
		</button>
	</form>

	<div class="block_head mt-4">
		Настройка форумов
	</div>
	<div class="table-responsive mb-0">
		<table class="table table-bordered">
			<thead>
				<tr>
					<td colspan="2">Изображение</td>
					<td>Название</td>
					<td>Описание</td>
					<td width="10%">Порядок</td>
					<td width="10%">Действие</td>
				</tr>
			</thead>
			<tbody id="forums{id}">
				<tr>
					<td colspan="10">
						<div class="loader"></div>
						<script>load_forums('{id}');</script>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>