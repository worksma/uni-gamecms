<div class="col-lg-9 order-is-first">
	<div class="row profile-page">
		<div class="col-lg-4">
			<div class="block profile">
				<div class="div-frame mb-4"><div class="div-avatar-frame">{image-frame}</div><img src="../{avatar}"></div>
				
				<div class="vertical-navigation">
					<ul>
						{menu}
					</ul>
				</div>

				<div id="result_profile"></div>
			</div>

			<div class="block">
				<div class="block_head">
					<a href="../friends?id={profile_id}">
						Друзья <small>[все]</small>
					</a>
				</div> 
				<div id="friends">
					{friends}
				</div>
			</div>
		</div>

		<div class="col-lg-8">
				<div class="block">
					<?$lvl = get_user_experience(['id' => $_GET['id'] ? $_GET['id'] : $_SESSION['id']]);?>
					<div class="profile-level text-center">
						Уровень
						<span class="level my-4" style="border-color:#<?=$lvl->color;?>;">
							<span class="num">
								<?=$lvl->level;?>
							</span>
						</span>
						<?=$lvl->experience;?> ед. опыта
						<div class="progress">
							<div class="progress-bar bg-info" role="progressbar" style="width: <?=$lvl->percent;?>%" aria-valuenow="<?=$lvl->percent;?>" aria-valuemin="0" aria-valuemax="100"></div>
						</div>
						<small><?=$lvl->remains;?> ед. опыта до <?=$lvl->next_level;?>-го уровня</small>
					</div>
				</div>
			<div class="block profile">
				<div class="block_head">
					{login} {verification}
					<small style="position:unset;float:right;">{last_activity}</small>
				</div>
				<table class="table mb-0">
					<tbody>
						{if('{dell}' != '1')}
						<tr>
							<td colspan="2">
								<h4>Общая информация</h4>
							</td>
						</tr>
						<tr>
							<td>ID</td>
							<td>{profile_id}</td>
						</tr>
						<tr>
							<td>Группа</td>
							<td><span style="color: {group_color}">{group}</span></td>
						</tr>
						<tr>
							<td>Дата регистрации</td>
							<td>{regdate}</td>
						</tr>
						<tr>
							<td>Ник на сервере</td>
							<td>{nick}</td>
						</tr>
						<tr>
							<td colspan="2">
								<h4>Личные данные</h4>
							</td>
						</tr>
						<tr>
							<td>Имя</td>
							<td>{name}</td>
						</tr>
						<tr>
							<td>Дата рождения</td>
							<td>{birth}</td>
						</tr>
						{if(('{skype}' != '' && '{skype}' != '---') || ('{discord}' != '' && '{discord}' != '---') || '{telegram}' != '' || '{vk}' != '---' || '{steam_api}' != '0' || '{fb}' != '0')}
							<tr>
								<td colspan="2">
									<h4>Контактные данные</h4>
								</td>
							</tr>
							{if('{discord}' != '' && '{discord}' != '---')}
							<tr>
								<td>Профиль Discord</td>
								<td><a>{discord}</a></td>
							</tr>
							{/if}
							{if('{skype}' != '' && '{skype}' != '---')}
							<tr>
								<td>Логин Skype</td>
								<td><a title="Добавить в скайп" href="skype:{skype}?add">{skype}</a></td>
							</tr>
							{/if}
							{if('{telegram}' != '')}
							<tr>
								<td>Телеграм</td>
								<td><a title="Написать в телеграм" target="_blank" href="https://telegram.me/{telegram}">@{telegram}</a></td>
							</tr>
							{/if}
							{if('{vk}' != '---')}
							<tr>
								<td>Вконтакте</td>
								<td>
									<a title="Перейти в профиль Вконтакте" target="_blank" href="https://vk.com/{vk}" id="vk_user">
										<img src="../files/avatars/no_avatar.jpg" alt="">
										<span>Загрузка...</span>
									</a>
									<script>get_vk_profile_info('{vk_api}', '#vk_user img', '#vk_user span', '{vk}');</script>
								</td>
							</tr>
							{/if}
							{if('{steam_api}' != '0')}
							<tr>
								<td>Steam</td>
								<td>
									<a title="Перейти в профиль Steam" target="_blank" href="https://steamcommunity.com/profiles/{steam_api}/" id="steam_user">
										<img src="../files/avatars/no_avatar.jpg" alt="">
										<span>Загрузка...</span>
									</a>
									<script>get_user_steam_info('{steam_api}');</script>
								</td>
							</tr>
							{/if}
							{if('{fb}' != '0')}
							<tr>
								<td>Facebook</td>
								<td>
									<a title="Профиль в Facebook" target="_blank" id="fb_user">
										<img src="../files/avatars/no_avatar.jpg" alt="">
										<span>Загрузка...</span>
									</a>
									<script> get_fb_profile_info('{fb_api}', '{fb}', '#fb_user', '#fb_user img', '#fb_user span'); </script>
								</td>
							</tr>
							{/if}
						{/if}
						<tr>
							<td colspan="2">
								<h4>Активность на форуме</h4>
							</td>
						</tr> 
						<tr>
							<td>Сообщений</td>
							<td>{answers}</td>
						</tr>
						<tr>
							<td>Спасибок</td>
							<td>{thanks}</td>
						</tr>
						<tr>
							<td>Последняя тема</td>
							<td>{if('{topic_id}' == '0')}Пользователь не просматривал форум{else}<a title="Перейти в тему" href="forum/topic?id={topic_id}">{topic_name}</a>{/if}</td>
						</tr>
						<tr>
							<td>Рейтинг</td>
							<td>{reit}</td>
						</tr>
						{else}
						<tr>
							<td colspan="2">
								<h4>Пользователь удален</h4>
							</td>
						</tr>
						{/if}
					</tbody>
				</table>
			</div>
		</div>

		<div class="col-lg-12">
			<div class="block block-table">
				<div class="block_head">
					Привилегии
				</div> 
				<div class="table-responsive mb-0">
					<table class="table table-condensed table-bordered admins">
						<thead>
							<tr>
								<td>#</td>
								<td>Сервер</td>
								<td>Идентификатор</td>
								<td>Тип</td>
								<td>Услуги</td>
							</tr>
						</thead>
						<tbody id="admins">
							{func Widgets:user_admins('{profile_id}')}
						</tbody>
					</table>
				</div>
			</div>

			{if('{dell}' != '1')}
			<div class="block">
				<div class="block_head">
					Стена
				</div> 
				{if('{checker}' != '1')}
				<div id="add_new_comments">
					<textarea id="text" maxlenght="1000"></textarea>

					<div class="smile_input_forum mt-3">
						<input id="send_btn" class="btn btn-outline-primary" type="button" onclick="send_user_comment({profile_id});" value="Отправить"></input>
						<div id="smile_btn" class="smile_btn" data-container="body" data-toggle="popover" data-placement="top" data-content="empty"></div>
					</div>
				</div>
				<script>
					$(document).ready(function() {
						init_tinymce("text", "lite", "{file_manager_theme}", "{file_manager}", "{{md5($conf->code)}}");
						get_smiles('#smile_btn');
					});

					$('#smile_btn').popover({ html: true, animation: true, trigger: "click" });
					$('#smile_btn').on('show.bs.popover', function () {
						$(document).mouseup(function (e) {
							var container = $(".popover-body");
							if (container.has(e.target).length === 0){
								$('#smile_btn').popover('hide');
								selected = 'gcms_smiles';
							}
						});
					});

					function set_smile(elem){
						var smile =  "<img src=\""+$(elem).attr("src")+"\" class=\"g_smile\" height=\"20px\" width=\"20px\">";
						tinymce.activeEditor.insertContent(smile);
						$('#smile_btn').popover('hide');
						selected = 'gcms_smiles';
					}
					function set_sticker(elem){
						var text = "sticker"+$(elem).attr("src");
						text = encodeURIComponent(text);
						send_user_comment({profile_id}, text);
						$('#smile_btn').popover('hide');
						selected = 'gcms_smiles';
					}
				</script>
				{/if}
				<div id="comments" class="mt-3">
					<div class="loader"></div>
				</div>
				<script>load_users_comments({profile_id},'first');</script>
			</div>
			{/if}
		</div>
	</div>
</div>
<div class="col-lg-3 order-is-last">
	{if(is_auth())}
		{include file="/home/navigation.tpl"}
	{else}
		{include file="/index/authorization.tpl"}
	{/if}

	<div class="block">
		<div class="block_head">
			Сообщения на форуме
		</div>
		<div id="last_activity">
			{func Widgets:user_forum_activity('{profile_id}', '3')}
		</div>
	</div>

	<div class="block">
		<div class="block_head">
			Заявки на разбан
		</div>
		<div id="mybans">
			{func Widgets:user_bans('{profile_id}', '3')}
		</div>
	</div>

	{if(is_auth())}
		{include file="/home/sidebar_secondary.tpl"}
	{else}
		{include file="/index/sidebar_secondary.tpl"}
	{/if}
</div>