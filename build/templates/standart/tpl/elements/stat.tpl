<tr id="sid{id}" data-target="#modal{id}" data-toggle="modal" {if('{army_enable}' == '2')} onclick="load_wstats({id},{server},'{authid}');load_mstats({id},{server},'{authid}');" {/if}>
	<td>{place}</td>
	<td>{nick}</td>
	<td>{frags}</td>
	<td>{deaths}</td>
	<td>{headshots}</td>
	{if('{type}' == '2')}
		<td>{rank}</td>
	{/if}
	{if(in_array({type}, array(1,2,3,4,5)))}
		<td>
		{if(in_array({type}, array(1,2,3,4)))}
			<span class="label label-{skill_color}">{skill_name} ({skill})</span>
		{else}
			{skill}
		{/if}
		</td>
	{/if}
	{if('{type}' == '6')}
	<td>
		<img src="{rank_img}" alt="{rank_name}" title="{rank_name}" class="rank">
	</td>
	{/if}
</tr>
<tr class="hidden-tr">
	<td>
		<div id="modal{id}" class="modal fade">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
						<h4 class="modal-title">Статистика игрока</h4>
					</div>
					<div class="modal-body" id="statinfo{id}">
						<div class="row">
							{if(is_worthy_specifically("h", {server}) || '{reset_stats}' == '1')}
							<div class="col-lg-12">
								<div class="table-responsive">
									<table class="table table-bordered">
										<tbody>
											<tr>
												<td><b>Операции</b></td>
												<td>
													{if('{reset_stats}' == '1')}
													<button class="btn btn-outline-primary mr-2" onclick="reset_stats({id}, {server})">Обнулить статистику - {reset_stats_price} {{$messages['RUB']}}</button>
													{/if}
													{if(is_worthy_specifically("h", {server}))}
													<button class="btn btn-outline-primary" onclick="dell_user_stats({id}, {server})">Удалить</button>
													{/if}
												</td>
											</tr>		
										</tbody>
									</table>
								</div>
							</div>
							{/if}
							<div class="col-lg-6">
								<div class="table-responsive">
									<table class="table table-bordered">
										<tr>
											<td colspan="2"><b>Общая информация</b></td>
										</tr>
										<tr>
											<td><b> Место в статистике: </b></td>
											<td>{place}</td>
										</tr>
										<tr>
											<td><b> Ник: </b></td>
											<td>{nick}</td>
										</tr>
										{if(in_array({type}, array(1,2,3,5,6)))}
											{if(is_worthy_specifically("h", {server}))}
											<tr>
												<td><b> Steam ID: </b></td>
												<td>{authid}</td>
											</tr>
											{/if}
										{/if}
										{if(in_array({type}, array(1,2,3,4,5)))}
										<tr>
											<td><b> Skill: </b></td>
											<td>
											{if(in_array({type}, array(1,2,3,4)))}
												<span class="label label-{skill_color}">{skill_name} ({skill})</span>
											{else}
												{skill}
											{/if}
											</td>
										</tr>
										{/if}
										<tr>
											<td><b> Убийств: </b></td>
											<td>{frags}</td>
										</tr>
										<tr>
											<td><b> Смертей: </b></td>
											<td>{deaths}</td>
										</tr>
										<tr>
											<td><b> Убийств в голову: </b></td>
											<td>{headshots}</td>
										</tr>
										{if(in_array({type}, array(1,2,3,4,5)))}
										<tr>
											<td><b> Убийств своих: </b></td>
											<td>{teamkills}</td>
										</tr>
										{/if}
										<tr>
											<td><b> Выстрелов: </b></td>
											<td>{shots}</td>
										</tr>
										<tr>
											<td><b> Попаданий: </b></td>
											<td>{hits}</td>
										</tr>
										{if('{type}' == '6')}
										<tr>
											<td><b> Очки: </b></td>
											<td>{value}</td>
										</tr>
										{/if}
										{if(in_array({type}, array(1,2,4,5)))}
										<tr>
											<td><b> Суицидов: </b></td>
											<td>{suicide}</td>
										</tr>
										{/if}
										{if(in_array({type}, array(1,2,3)))}
										<tr>
											<td><b> Урон: </b></td>
											<td>{damage}</td>
										</tr>
										<tr>
											<td><b> Попыток разминирования: </b></td>
											<td>{defusing}</td>
										</tr>
										{/if}
										{if(in_array({type}, array(1,2,3,5)))}
											<tr>
												<td><b> Разминирований: </b></td>
												<td>{defused}</td>
											</tr>
											<tr>
												<td><b> Поставленных бомб: </b></td>
												<td>{planted}</td>
											</tr>
											<tr>
												<td><b> Взорванных бомб: </b></td>
												<td>{explode}</td>
											</tr>
											{if('{army_enable}' == '2')}
											<tr>
												<td><b> Спасено заложников: </b></td>
												<td>{hostages}</td>
											</tr>
											<tr>
												<td><b> Помог убить врагов: </b></td>
												<td>{assist}</td>
											</tr>
											{/if}
										{/if}
									</table>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="table-responsive">
									<table class="table table-bordered">
										<tr>
											<td colspan="2"><b>Дополнительная информация</b></td>
										</tr>
										<tr>
											<td><b> Убийства/Смерти: </b></td>
											<td>
												<div class="progress">
													<div class="progress-bar bg-info" style="width: {procent1}%"></div>
													<span>{procent1}%</span>
												</div>
											</td>
										</tr>
										<tr>
											<td><b> Убийства в голову: </b></td>
											<td>
												<div class="progress">
													<div class="progress-bar bg-info" style="width: {procent2}%"></div>
													<span>{procent2}%</span>
												</div>
											</td>
										</tr>
										{if(in_array({type}, array(1,2,3,4,5)))}
										<tr>
											<td><b> Атака своих: </b></td>
											<td>
												<div class="progress">
													<div class="progress-bar bg-info" style="width: {procent3}%"></div>
													<span>{procent3}%</span>
												</div>
											</td>
										</tr>
										{/if}
										<tr>
											<td><b> Точность: </b></td>
											<td>
												<div class="progress">
													<div class="progress-bar bg-info" style="width: {procent4}%"> </div>
													<span>{procent4}%</span>
												</div>
											</td>
										</tr>
										{if(in_array({type}, array(1,2,3,4,5)))}
										<tr>
											<td><b> Время в игре: </b></td>
											<td>{time}</td>
										</tr>
										{/if}
										<tr>
											<td><b> Последнее посещение: </b></td>
											<td>{date}</td>
										</tr>
										{if(in_array({type}, array(1,2,3)))}
										<tr>
											<td><b> Заходов: </b></td>
											<td>{connects}</td>
										</tr>
										{/if}
										{if(in_array({type}, array(1,2,3,5)))}
										<tr>
											<td><b> Сыграно раундов: </b></td>
											<td>{rounds}</td>
										</tr>
										<tr>
											<td><b> Выиграл за Т: </b></td>
											<td>{wint}</td>
										</tr>
										<tr>
											<td><b> Выиграл за CT: </b></td>
											<td>{winct}</td>
										</tr>
										{/if}
										{if('{type}' == '4')}
										<tr>
											<td><b> Серия убийств: </b></td>
											<td>{kill_streak}</td>
										</tr>
										<tr>
											<td><b> Серия смертей: </b></td>
											<td>{death_streak}</td>
										</tr>
										{/if}
									</table>
								</div>
								{if('{type}' == '2')}
								<div class="table-responsive mb-0">
									<table class="table table-bordered">
										<tr>
											<td colspan="2"><b>Армия</b></td>
										</tr>
										<tr>
											<td><b> Ранг: </b></td>
											<td>{rank}</td>
										</tr>
										<tr>
											<td><b> Опыт: </b></td>
											<td>{level}</td>
										</tr>
										<tr>
											<td colspan="2"><b>Бонусы</b></td>
										</tr>
										<tr>
											<td><b> Взрывная граната: </b></td>
											<td>{ar_bonus_he}</td>
										</tr>
										<tr>
											<td><b> Слеповая граната: </b></td>
											<td>{ar_bonus_flash}</td>
										</tr>
										<tr>
											<td><b> Дымовая граната: </b></td>
											<td>{ar_bonus_smoke}</td>
										</tr>
										<tr>
											<td><b> Дефуз: </b></td>
											<td>{ar_bonus_defuse}</td>
										</tr>
										<tr>
											<td><b> Ночное видение: </b></td>
											<td>{ar_bonus_nv}</td>
										</tr>
										<tr>
											<td><b> Дополнительные HP: </b></td>
											<td>{ar_bonus_hp}</td>
										</tr>
										<tr>
											<td><b> Дополнительные AP: </b></td>
											<td>{ar_bonus_armor}</td>
										</tr>
										<tr>
											<td><b> Флаги: </b></td>
											<td>{ar_bonus_flags}</td>
										</tr>
										<tr>
											<td><b> Дополнительный урон: </b></td>
											<td>{ar_bonus_damage}</td>
										</tr>
									</table>
									{/if}
								</div>
							</div>
							{if('{army_enable}' == '2')}
							<div class="col-lg-12">
								<h4 class="modal-title">
									Используемое оружие
								</h4>
								<div class="table-responsive mt-3">
									<table class="table table-bordered">
										<thead>
											<tr>
												<td>Оружие</td>
												<td>Имя</td>
												<td>Убийств</td>
												<td>Выстрелов</td>
												<td>Урон</td>
												<td>Голова</td>
												<td>Грудь</td>
												<td>Тело</td>
												<td>Пр. рука</td>
												<td>Лев. рука</td>
												<td>Пр. нога</td>
												<td>Лев. нога</td>
											</tr>
										</thead>
										<tbody id="wstats{id}"></tbody>
									</table>
								</div>

								<h4 class="modal-title">
									Статистика по картам
								</h4>
								<div class="table-responsive mb-0 mt-3">
									<table class="table table-bordered">
										<thead>
											<tr>
												<td></td>
												<td>Название</td>
												<td>Убийств</td>
												<td>Смертей</td>
												<td>HeadShots</td>
												<td>Раундов</td>
												<td>Время в игре</td>
											</tr>
										</thead>
										<tbody id="mstats{id}"></tbody>
									</table>
								</div>
							</div>
							{/if}
						</div>
					</div>
				</div>
			</div>
		</div>
	</td>
</tr>