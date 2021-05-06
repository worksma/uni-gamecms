<div class="page">
	<div class="block">
		<div class="block_head">
			Настройки
		</div> 
		<div class="form-group mb-10">
			<div class="btn-group" data-toggle="buttons">
				<label class="btn btn-default {act_st}" onclick="change_value('config','stat','1','1'); send_value('input_privacy','1');" checked>
					<input type="radio">
					Включить
				</label>

				<label class="btn btn-default {act2_st}" onclick="change_value('config','stat','2','1'); send_value('input_privacy','2');">
					<input type="radio">
					Выключить
				</label>
			</div>
		</div>

		<div class="input-group">
			<span class="input-group-btn">
				<button class="btn btn-default" type="button" onclick="edit_stat_number();">Изменить</button>
			</span>
			<input type="text" class="form-control" id="stat_number" maxlength="5" autocomplete="off" value="{stat_number}">
		</div>
		<small class="f-r c-868686">Сколько последних визитов будет хранить лог</small>
		<div id="edit_stat_number_result"></div><br> 
		<button class="btn btn-default" type="button" onclick="dell_stat_log();">Очистить статистику</button>
	</div>
	<div class="block">
		<div class="block_head">
			Лог статистики
		</div> 
		<div class="table-responsive mb-0">
			<table class="table table-bordered">
				<thead>
					<tr>
						<td>#</td>
						<td>Время и дата</td>
						<td>Данные о посетителе</td>
						<td>IP/прокси</td>
						<td>Посещенный URL</td>
					</tr>
				</thead>
				<tbody id="stat">
					{data}
				</tbody>
			</table>
		</div>
	</div>
</div>