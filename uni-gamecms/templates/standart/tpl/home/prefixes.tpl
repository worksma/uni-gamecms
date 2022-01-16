<div class="col-lg-9 order-is-first">
	<div class="block">
		<div class="block_head">
			Покупка префиксов
		</div>

		<div class="row">
			<div class="col-lg-12" id="buy_area">
				<div class="form-group">
					<label><h4>Выберите сервер</h4></label>
					<select class="form-control" id="serv" onchange="getTermPrefixes();">{servers}</select>
				</div>

				<div class="form-group">
					<label><h4>Выберите срок аренды</h4></label>
					<select class="form-control" id="term">{term}</select>
				</div>

				<div class="form-group">
					<label><h4>Метод привязки</h4></label>
					<select class="form-control" id="binding" onchange="change_store_bind_type($('#binding option:selected').val());">
						<option selected disabled>Выберите способ привязки</option>
						{if('{bind_nick_pass}' == '1')}
						<option value="1">Ник + пароль</option>
						{/if}
						
						{if('{bind_steam}' == '1')}
						<option value="2">SteamID</option>
						{/if}
						
						{if('{bind_steam_pass}' == '1')}
						<option value="3">SteamID + пароль</option>
						{/if}
					</select>
					
					{if('{bind_nick_pass}' == '1')}
						<input type="text" class="form-control disp-n" maxlength="32" id="player_nick" placeholder="Введите свой ник" value="{nick}">
					{/if}
					
					{if('{bind_steam}' == '1' || '{bind_steam_pass}' == '1')}
						<input type="text" class="form-control disp-n" maxlength="32" id="player_steam_id" placeholder="Введите свой STEAM ID" value="{steam_id}">
					{/if}
					
					{if('{bind_nick_pass}' == '1' || '{bind_steam_pass}' == '1')}
						<input type="text" class="form-control disp-n" maxlength="32" id="player_pass" placeholder="Придумайте пароль">
					{/if}
				</div>

				<div class="form-group">
					<label><h4>Желаемый префикс</h4></label>
					<input type="text" class="form-control" maxlength="32" id="player_prefix" placeholder="Придумайте текст">
				</div>

				{if(is_auth())}
				<div class="form-group">
					<label><h4>Условия</h4></label>
					<div class="form-check">
						<input class="form-check-input" id="store_checbox" data-status="2" type="checkbox" onclick="if(this.checked) {$('#store_buy_btn').attr('disabled', false);}else{$('#store_buy_btn').attr('disabled', true);}">
						<label class="form-check-label" for="store_checbox">
							Я ознакомлен с <a target="_blank" href="../pages/rules">правилами</a> проекта и согласен с ними
						</label>
					</div>
					
					<div id="buy_result" class="mt-3"></div>
					<div id="button" class="mt-3">
						<button id="store_buy_btn" class="btn btn-primary" onclick="buyPrefix();" disabled>Купить</button>
					</div>
				</div>
				{else}
					<div class="noty-block error">
						<p>Авторизуйтесь, чтобы приобрести услугу</p>
					</div>
				{/if}
			</div>
		</div>
	</div>
</div>

<div class="col-lg-3 order-is-last">
	{if(is_auth())}
		{include file="/home/navigation.tpl"}
		{include file="/home/sidebar_secondary.tpl"}
	{else}
		{include file="/index/authorization.tpl"}
		{include file="/home/sidebar_secondary.tpl"}
	{/if}
</div>