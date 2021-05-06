<div class="page">
	<div class="block">
		<div class="block_head">
			Группа по умолчанию
		</div>
		<div class="input-group">
			<span class="input-group-btn">
				<button class="btn btn-default" type="button" onclick="change_group();">Изменить</button>
			</span>
			<select id="users_group" class="form-control">
				{users_groups}
			</select>
		</div>
	</div>

	<div class="block">
		<div class="block_head">
			Описание флагов
		</div>
		<div class="col-md-6">
			<b>a</b> - все права обычного пользователя <br>
			<hr class="ma-0 mt-10 mb-10">
			<b>m</b> - выдача / отбор денег / видит баланс других <br>
			<b>c</b> - установка персональной скидки <br>
			<hr class="ma-0 mt-10 mb-10">
			<b>d</b> - удаление / редактирование сообщений в чате / удаление событий <br>
			<b>y</b> - удаление сообщений со стен пользователей <br>
			<hr class="ma-0 mt-10 mb-10">
			<b>b</b> - создание новостей <br>
			<b>q</b> - редактирование / удаление новостей / удаление комментариев <br>
			<hr class="ma-0 mt-10 mb-10">
			<b>f</b> - редактирование пользователей (имеется возможность установки пользователям группы без флага n) <br>
			<b>n</b> - возможность установки пользователям любой группы <br>
			<b>g</b> - удаление пользователей <br>
			<hr class="ma-0 mt-10 mb-10">
			<b>p</b> - рассмотрение тикетов <br>
			<b>l</b> - удаление тикетов <br>
		</div>
		<div class="col-md-6">
			<b>t</b> - создание / удаление / редактирование разделов / форумов <br>
			<b>w</b> - создание тем на форуме <br>
			<b>e</b> - редактирование / удаление тем на форуме <br>
			<b>r</b> - удаление / редактирование сообщений на форуме <br>
			<hr class="ma-0 mt-10 mb-10">
			<b>i</b> - рассмотрение заявок на разбан <br>
			<b>o</b> - удаление заявок на разбан <br>
			<b>u</b> - удаление комментариев к банам <br>
			<hr class="ma-0 mt-10 mb-10">
			<b>s</b> - бан/кик игроков на сервере, разбан через банлист <br>
			<b>j</b> - управление администраторами сервера <br>
			<b>h</b> - удаление игровой статистики пользователя<br>
			<hr class="ma-0 mt-10 mb-10">
			<p class="text-danger m-0"><b>z</b> - временный бан. Снимается путем изменения группы.</p>
			<p class="text-danger m-0"><b>x</b> - вечный бан. Не использовать в качестве теста! Банит навсегда,
			                                    бан не снимается, сайт больше не будет доступен пользователям,
			                                    в группе которых будет этот флаг. Должен быть включен глобальный бан
			</p>
		</div>
		<div class="col-md-12">
			<hr>
			<div class="bs-callout bs-callout-info">
				<p>
					Флаги <b>i, o, u, s, j, h</b> имеют способность ограничивать доступ до конкретных серверов, к примеру, если в правах группы задать: 
					<br><b>j</b> - пользователи группы будут иметь доступ к управлению администраторами <b>всех серверов</b>,
					<br><b>j1</b> - пользователи группы будут иметь доступ к управлению администраторами сервера с <b>ID 1</b>,
					<br><b>j1:2:3</b> - пользователи группы будут иметь доступ к управлению администраторами серверов с <b>ID 1, ID 2, ID 3</b>.
					<br>оставшиеся флаги настраиваются аналогично, ID серверов можно узнать в настройках серверов.
				</p>
			</div>
		</div>
		<!-- kv -->
	</div>

	<div class="block">
		<div class="block_head">
			Добавление группы
		</div>
		<div class="col-md-6">
			Название группы
			<input type="text" class="form-control mb-10" id="name" maxlength="30" autocomplete="off" placeholder="Введите название">
			Права группы
			<input type="text" class="form-control mb-10" id="rights" maxlength="512" autocomplete="off" placeholder="Введите флаги">
			
			<div class="row">
				<div class="col-md-3">
					Цвет
					<input type="text" class="form-control mb-10" id="color" value="#FFFFFF">
				</div>
				<div class="col-md-9">
					Дополнительный стиль
					<input type="text" class="form-control mb-10" id="style" maxlength="240" placeholder="Код CSS">
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div id="colorpicker"></div>
			<script>
				$(document).ready(function() {
					$('#colorpicker').farbtastic('#color');
				});
			</script>
		</div>
		<div class="col-md-12">
			<div id="result"></div>
			<button class="btn2" onclick="add_group();">Добавить</button>
		</div>
	</div>

	<div class="block">
		<div class="block_head">
			Редактирование групп
		</div>
		<div id="groups">
			<script>get_groups();</script>
		</div>
	</div>
</div>