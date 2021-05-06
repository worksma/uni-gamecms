<div class="page">
	<div class="row">
		<div class="col-md-12">
			<div class="block">
				<div class="block_head">
					Баланс
				</div>
				Баланс Вашего банка составляет
				<a class="td-u c-p" title="Подробнее" onclick="load_bank_info('1');">{bank1}</a>
				 {{$messages['RUB']}}, за данный месяц:
				<a class="td-u c-p" title="Подробнее" onclick="load_bank_info('2');">{bank2}</a>
				 , за прошлый месяц: 
				<a class="td-u c-p" title="Подробнее" onclick="load_bank_info('3');">{bank3}</a>
			</div>
		</div>

		<div class="col-md-6">
			<div class="block">
				<div class="block_head">
					Основные настройки
				</div>
				<b>Минимальная сумма для пополнения</b>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" onclick="edit_min_amount();">Изменить</button>
						</span>
						<input type="number" class="form-control" id="min_amount" maxlength="5" autocomplete="off" placeholder="от 0 до 99999" value="{min_amount}">
					</div>
					<div id="edit_min_amount_result"></div>
				</div>
				<hr>
				<b>Скидка на все услуги в %</b>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" onclick="edit_discount();">Изменить</button>
						</span>
						<input type="number" class="form-control" id="discount" maxlength="2" autocomplete="off" placeholder="от 1 до 99" value="{discount}">
					</div>
					<div id="edit_discount_result"></div>
				</div>
				<hr>
				<b>Начальный баланс пользователя</b>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" onclick="edit_stand_balance();">Изменить</button>
						</span>
						<input type="number" class="form-control" id="stand_balance" maxlength="5" autocomplete="off" placeholder="от 0 до 99999" value="{stand_balance}">
					</div>
					<div id="edit_stand_balance_result"></div>
				</div>
				<hr>
				<b>Функция возврата средств</b>
				<div class="form-group">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {rs_act}" onclick="change_value('config__secondary','return_services','1','1');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {rs_act2}" onclick="change_value('config__secondary','return_services','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<hr>
				<b>Ваучеры</b><br>
				<button class="btn btn-default" data-target="#vouchers" data-toggle="modal" onclick="load_vouchers('first');">Открыть</button>
			</div>
			<div class="block">
				<div class="block_head">
					Контактные данные админов
				</div>
				<div class="form-group">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {act}" onclick="change_value('config','cont','1','1');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {act2}" onclick="change_value('config','cont','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="bs-callout bs-callout-info mt-10">
					<p>Поле ID Вконтакте становится обязательным для заполнения при приобретении прав.</p>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					Запрещенные идентификаторы
				</div>
				<div class="col-md-6">
					<button class="btn btn-default" data-target="#bad_nicks" data-toggle="modal" onclick="load_bad_nicks();">Добавить | редактировать</button>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<div class="btn-group" data-toggle="buttons">
							<label class="btn btn-default {bn_act}" onclick="change_value('config__secondary','bad_nicks_act','1','1');">
								<input type="radio">
								Вкл
							</label>

							<label class="btn btn-default {bn_act2}" onclick="change_value('config__secondary','bad_nicks_act','2','1');">
								<input type="radio">
								Выкл
							</label>
						</div>
					</div>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					Стикеры
				</div>
				<div class="col-md-6">
					<b>Цена: </b>
					<div class="input-group">
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" onclick="edit_stickers();">Изменить</button>
						</span>
						<input type="number" class="form-control" id="price4" maxlength="5" autocomplete="off" value="{price4}" placeholder="Цена для стикеров">
					</div>
					<div id="edit_stickers_result"></div>
				</div>
				<div class="col-md-6">
					<b>Наборы стикеров: </b><br>
					<button class="btn btn-default" data-target="#stickers" data-toggle="modal" onclick="load_stickers();">Добавить | Редактировать</button>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					Бонусы при пополнении баланса
				</div>
				<div class="col-md-6">
					<button class="btn btn-default" data-target="#bonuses" data-toggle="modal" onclick="load_bonuses();">Добавить | редактировать</button>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<div class="btn-group" data-toggle="buttons">
							<label class="btn btn-default {bns_act}" onclick="change_value('config__secondary','bonuses','1','1');">
								<input type="radio">
								Вкл
							</label>

							<label class="btn btn-default {bns_act2}" onclick="change_value('config__secondary','bonuses','2','1');">
								<input type="radio">
								Выкл
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="block">
				<div class="block_head">
					Реферальная программа
				</div>
				<div class="form-group">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {ref_act}" onclick="change_value('config__prices','referral_program ','1','1');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {ref_act2}" onclick="change_value('config__prices','referral_program ','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>

					<div class="input-group mt-10">
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" onclick="edit_referral_percent();">Изменить</button>
						</span>
						<input type="number" class="form-control" id="referral_percent" maxlength="2" autocomplete="off" placeholder="от 0 до 99" value="{referral_percent}">
					</div>
					<div id="edit_referral_percent_result"></div>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					Ограничение на редактирование услуг
				</div>
				<b>Смена пароля разрешена раз в (дней)</b>
				<div class="form-group mb-10">
					<div class="input-group">
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" onclick="edit_col_pass();">Изменить</button>
						</span>
						<input type="text" class="form-control" id="col_pass" maxlength="3" autocomplete="off" value="{col_pass}">
					</div>
					<small class="f-r c-868686">0 - никогда</small>
					<div id="edit_col_pass_result"></div>
				</div>
				<b>Смена идентификатора разрешена раз в (дней)</b>
				<div class="form-group mb-10">
					<div class="input-group">
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" onclick="edit_col_nick();">Изменить</button>
						</span>
						<input type="text" class="form-control" id="col_nick" maxlength="23" autocomplete="off" value="{col_nick}">
					</div>
					<small class="f-r c-868686">0 - никогда</small>
					<div id="edit_col_nick_result"></div>
				</div>
				<b>Смена типа привязки разрешена раз в (дней)</b>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-btn">
							<button class="btn btn-default" type="button" onclick="edit_col_type();">Изменить</button>
						</span>
						<input type="text" class="form-control" id="col_type" maxlength="3" autocomplete="off" value="{col_type}">
					</div>
					<small class="f-r c-868686">0 - никогда</small>
					<div id="edit_col_type_result"></div>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					Цены на разбан
				</div>
				<input type="number" class="form-control" id="price1" maxlength="5" autocomplete="off" value="{price1}" placeholder="Цена для бана сроком от 1сек до 7дней, введите 0, чтобы выключить">
				<small class="f-r c-868686">Цена для бана сроком от 1сек до 7дней, введите 0, чтобы выключить</small>
				<input type="number" class="form-control" id="price2" maxlength="5" autocomplete="off" value="{price2}" placeholder="Цена для бана сроком более 7 дней, введите 0, чтобы выключить">
				<small class="f-r c-868686">Цена для бана сроком более 7 дней, введите 0, чтобы выключить</small>
				<input type="number" class="form-control" id="price3" maxlength="5" autocomplete="off" value="{price3}" placeholder="Цена для перманентного бана, введите 0, чтобы выключить">
				<small class="f-r c-868686">Цена для перманентного бана, введите 0, чтобы выключить</small>

				<button class="btn btn-default mt-5" type="button" onclick="edit_unban();">Изменить</button>
				<div id="edit_unban_result"></div>
			</div>

			<div class="block">
				<div class="block_head">
					Цены на размут
				</div>
				<input type="number" class="form-control" id="price2_1" maxlength="5" autocomplete="off" value="{price2_1}" placeholder="Цена для мута сроком от 1сек до 7дней, введите 0, чтобы выключить">
				<small class="f-r c-868686">Цена для мута сроком от 1сек до 7дней, введите 0, чтобы выключить</small>
				<input type="number" class="form-control" id="price2_2" maxlength="5" autocomplete="off" value="{price2_2}" placeholder="Цена для мута сроком более 7 дней, введите 0, чтобы выключить">
				<small class="f-r c-868686">Цена для мута сроком более 7 дней, введите 0, чтобы выключить</small>
				<input type="number" class="form-control" id="price2_3" maxlength="5" autocomplete="off" value="{price2_3}" placeholder="Цена для перманентного мута, введите 0, чтобы выключить">
				<small class="f-r c-868686">Цена для перманентного мута, введите 0, чтобы выключить</small>

				<button class="btn btn-default mt-5" type="button" onclick="edit_unmute();">Изменить</button>
				<div id="edit_unmute_result"></div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="block">
				<div class="block_head">
					Операции пользователей
				</div>
				<div class="table-responsive mb-0">
					<table class="table table-bordered">
						<thead>
							<tr>
								<td>#</td>
								<td>Тип</td>
								<td>Сумма</td>
								<td>Пользователь</td>
								<td>Дата</td>
							</tr>
						</thead>
						<tbody id="operations">
							<tr>
								<td colspan="5">
									<br><center><img src="{site_host}templates/admin/img/loader.gif"></center><br>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<script>get_shilings_operations('first');</script>
			</div>
		</div>
	</div>
</div>

<div id="bank1" class="modal fade">
	<div class="modal-dialog modal-lg2">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Статистика за все время ({bank1}{{$messages['RUB']}})</h4>
			</div>
			<div class="modal-body">
				<div id="bank_info1"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="bank2" class="modal fade">
	<div class="modal-dialog modal-lg2">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Статистика за данный месяц ({bank2}{{$messages['RUB']}})</h4>
			</div>
			<div class="modal-body">
				<div id="bank_info2"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="bank3" class="modal fade">
	<div class="modal-dialog modal-lg2">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Статистика за прошлый месяц ({bank3}{{$messages['RUB']}})</h4>
			</div>
			<div class="modal-body">
				<div id="bank_info3"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="bad_nicks" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Запрещенные идентификаторы</h4>
			</div>
			<div class="modal-body">
				<div class="bs-callout bs-callout-info mb-10 fs-14">
					Данная функция запрещает покупку привилегий на указанные Вами идентификаторы.<br>
					<br>
					Вы можете указать шаблон для запрета идентификаторов при помощи символа: <b>{%}</b>. Пример:<br>
					<b>{%}bad nick</b> - запретит все идентификаторы, которые заканчиваются на <b>bad nick</b> <br>
					<b>bad nick{%}</b> - запретит все идентификаторы, которые начинаются на <b>bad nick</b> <br>
					<b>{%}bad nick{%}</b> - запретит все идентификаторы, в которых встречается строка <b>bad nick</b> <br>
				</div>

				<form id="bad_nicks_list"></form>
				<button class="btn btn-default mt-5 f-l" onclick="save_bad_nicks();">Сохранить</button>
				<button class="btn btn-default mt-5 ml-5 f-l" onclick="add_nick_input();">Добавить</button>
				<div class="clearfix"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="bonuses" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Бонусы при пополнении баланса</h4>
			</div>
			<div class="modal-body">
				<div class="bs-callout bs-callout-info mb-10 fs-14">
					Данная функция позволяет пользователям получать бонусы при пополнении счета. К примеру, при настройках с диапазоном от 100 до 500{{$messages['RUB']}} и бонусом в 25{{$messages['RUB']}}, пользователь, при пополнеии счета на 200{{$messages['RUB']}} получит дополнительно 25{{$messages['RUB']}} как бонус. Второй пример: диапазон от 100 до 300{{$messages['RUB']}}, бонус в 5%, при пополнении счета на 200{{$messages['RUB']}} пользователь получит 5% от 200 как бонус - 10{{$messages['RUB']}}
				</div>

				<form id="bonuses_list">
				</form>

				<button class="btn btn-default mt-5 f-l" onclick="save_bonuses();">Сохранить</button>
				<button class="btn btn-default mt-5 ml-5 f-l" onclick="add_bonus_input();">Добавить</button>
				<div class="clearfix"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="stickers" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Стикеры</h4>
			</div>
			<div class="modal-body">
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="add_stickers();">Добавить</button>
					</span>
					<input type="text" class="form-control" id="stickers_name" maxlength="50" autocomplete="off" placeholder="Название">
				</div>
				<hr>
				<div class="panel-group" role="tablist" aria-multiselectable="true" id="stickers_body"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>

<div id="vouchers" class="modal fade">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Ваучеры</h4>
			</div>
			<div class="modal-body">
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="add_vouchers();">Добавить</button>
					</span>
					<input type="number" class="form-control" id="voucher_val" maxlength="5" autocomplete="off" placeholder="Сумма, которая будет выдана при активации ваучера">
					<input type="number" class="form-control" id="voucher_col" maxlength="2" autocomplete="off" placeholder="Количество ваучеров">
				</div>
				<div id="add_vouchers_result"></div>
				<hr>
				<div class="panel-group" role="tablist" aria-multiselectable="true">
					<table class="table table-bordered">
						<thead>
							<tr>
								<td>#</td>
								<td>Сумма</td>
								<td>Ключ</td>
								<td>Статус</td>
								<td>Удалить</td>
							</tr>
						</thead>
						<tbody id="vouchers_body">
							<tr><td colspan="4"><center><img src="{site_host}templates/admin/img/loader.gif"></center></td></tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
			</div>
		</div>
	</div>
</div>