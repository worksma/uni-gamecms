<div class="page">
	<div class="row">
		<div class="col-md-6">
			<!--[ PerfectMoney добавил Metal Messiah ]-->
			<div class="block">
				<div class="block_head">
					PerfectMoney
					<?if(time() < strtotime('2022-04-05')):?>
					<span class="badge">Новое</span>
					<?endif;?>
				</div>
				
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {perfectmoney_act}" onclick="change_value('config__bank','perfectmoney','1','1','perfectmoney_id,perfectmoney_secret');">
							<input type="radio"> Включить
						</label>
						
						<label class="btn btn-default {perfectmoney_act2}" onclick="change_value('config__bank','perfectmoney','2','1');">
							<input type="radio"> Выключить
						</label>
					</div>
				</div>
				
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="edit_perfectmoney();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="perfectmoney_id" maxlength="255" autocomplete="off" value="{perfectmoney_id}" placeholder="ID магазина">
					<input type="text" class="form-control" id="perfectmoney_secret" maxlength="255" autocomplete="off" value="{perfectmoney_secret}" placeholder="Секретный ключ">
				</div>
				<div id="edit_perfectmoney_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<p>
					<table>
						<tr>
							<td colspan="2">Необходимые данные:</td>
						</tr>
						<tr>
							<td colspan=2>Секретный ключ это MD5 от Альтернативная кодовая фраза в ВЕРХНЕМ регистре (32 печаных символа A-F, 0-9)</td>
						</tr>
						<tr>
							<td style="text-align: right">URL оповещ: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?pm=get</b></td>
						</tr>
						<tr>
							<td style="text-align: right">URL успеха: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
						</tr>
						<tr>
							<td style="text-align: right">URL неуспеха: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result=fail</b></td>
						</tr>
					</table>
					</p>
				</div>
			</div>
		
			<!--[ Lava ]-->
			<div class="block">
				<div class="block_head">
					Lava
				</div>
				
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {lava_act}" onclick="change_value('config__bank','lava','1','1','lava_wallet,lava_token');">
							<input type="radio"> Включить
						</label>
						
						<label class="btn btn-default {lava_act2}" onclick="change_value('config__bank','lava','2','1');">
							<input type="radio"> Выключить
						</label>
					</div>
				</div>
				
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="edit_lava();">Изменить</button>
					</span>
					
					<input type="text" class="form-control" id="lava_wallet" maxlength="255" autocomplete="off" value="{lava_wallet}" placeholder="Номер кошелька">
					<input type="text" class="form-control" id="lava_token" maxlength="255" autocomplete="off" value="{lava_token}" placeholder="Секретный ключ">
				</div>
			</div>
			
			<!--[ AmaraPay ]-->
			<div class="block">
				<div class="block_head">
					AmaraPay
				</div>
				
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {amarapay_act}" onclick="change_value('config__bank','amarapay','1','1','amarapay_id,amarapay_public,amarapay_secret');">
							<input type="radio"> Включить
						</label>
						
						<label class="btn btn-default {amarapay_act2}" onclick="change_value('config__bank','amarapay','2','1');">
							<input type="radio"> Выключить
						</label>
					</div>
				</div>
				
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-40-12" type="button" onclick="edit_amarapay();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="amarapay_id" maxlength="255" autocomplete="off" value="{amarapay_id}" placeholder="ID магазина">
					<input type="text" class="form-control" id="amarapay_secret" maxlength="255" autocomplete="off" value="{amarapay_secret}" placeholder="Секретный ключ">
					<input type="text" class="form-control" id="amarapay_public" maxlength="255" autocomplete="off" value="{amarapay_public}" placeholder="Публичный ключ">
				</div>
				
				<div class="bs-callout bs-callout-info mt-10">
					<p>
					<table>
						<tr>
							<td colspan="2">Необходимые данные:</td>
						</tr>
						<tr>
							<td style="text-align: right">URL оповещ: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?amarapay=get</b></td>
						</tr>
						<tr>
							<td style="text-align: right">URL успеха: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
						</tr>
						<tr>
							<td style="text-align: right">URL неуспеха: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result=fail</b></td>
						</tr>
					</table>
					</p>
				</div>
			</div>

			<!--[ FreeKassa ]-->
			<div class="block">
				<div class="block_head">
					FreeKassa
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {fknewact}" onclick="change_value('config__bank','fk_new','1','1','fk_new_login,fk_new_pass1,fk_new_pass2');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {fknewact2}" onclick="change_value('config__bank','fk_new','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-40-12" type="button" onclick="edit_freekassa('new');">Изменить</button>
					</span>
					<input type="text" class="form-control" id="fknew_login" maxlength="255" autocomplete="off" value="{fk_new_login}" placeholder="ID магазина">
					<input type="text" class="form-control" id="fknew_pass1" maxlength="255" autocomplete="off" value="{fk_new_pass1}" placeholder="Секретное слово">
					<input type="text" class="form-control" id="fknew_pass2" maxlength="255" autocomplete="off" value="{fk_new_pass2}" placeholder="Секретное слово 2">
				</div>
				<div id="edit_freekassanew_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<p>
					<table>
						<tr>
							<td colspan="2">Необходимые данные:</td>
						</tr>
						<tr>
							<td style="text-align: right">URL оповещ: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result_fk=get</b></td>
						</tr>
						<tr>
							<td style="text-align: right">URL успеха: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
						</tr>
						<tr>
							<td style="text-align: right">URL неуспеха: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result=fail</b></td>
						</tr>
					</table>
					</p>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					ROBOKASSA
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {rbact}" onclick="change_value('config__bank','rb','1','1','rb_login,rb_pass1,rb_pass2');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {rbact2}" onclick="change_value('config__bank','rb','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-40-12" type="button" onclick="edit_robokassa();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="rb_login" maxlength="255" autocomplete="off" value="{rb_login}" placeholder="Логин">
					<input type="text" class="form-control" id="rb_pass1" maxlength="255" autocomplete="off" value="{rb_pass1}" placeholder="Пароль 1">
					<input type="text" class="form-control" id="rb_pass2" maxlength="255" autocomplete="off" value="{rb_pass2}" placeholder="Пароль 2">
				</div>
				<div id="edit_robokassa_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<p>
						<table>
							<tr>
								<td colspan="2">Необходимые данные:</td>
							</tr>
							<tr>
								<td style="text-align: right">Result Url: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result_rb=get</b></td>
							</tr>
							<tr>
								<td style="text-align: right">Success Url: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
							</tr>
							<tr>
								<td style="text-align: right">Fail Url: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result=fail</b></td>
							</tr>
						</table>
					</p>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					InterKassa
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {ikact}" onclick="change_value('config__bank','ik','1','1','ik_login,ik_pass1');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {ikact2}" onclick="change_value('config__bank','ik','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="edit_interkassa();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="ik_login" maxlength="255" autocomplete="off" value="{ik_login}" placeholder="ID кассы">
					<input type="text" class="form-control" id="ik_pass1" maxlength="255" autocomplete="off" value="{ik_pass1}" placeholder="Секретный ключ">
				</div>
				<div id="edit_interkassa_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<p>
						<table>
							<tr>
								<td colspan="2">Необходимые данные:</td>
							</tr>
							<tr>
								<td style="text-align: right">URL оповещ: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result_ik=get</b></td>
							</tr>
							<tr>
								<td style="text-align: right">URL успеха: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
							</tr>
							<tr>
								<td style="text-align: right">URL ожидан: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
							</tr>
							<tr>
								<td style="text-align: right">URL неуспеха: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result=fail</b></td>
							</tr>
						</table>
					</p>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					Paysera
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {psact}" onclick="change_value('config__bank','ps','1','1','ps_num,ps_pass');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {psact2}" onclick="change_value('config__bank','ps','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="edit_paysera();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="ps_num" maxlength="255" autocomplete="off" value="{ps_num}" placeholder="Номер проекта">
					<input type="text" class="form-control" id="ps_pass" maxlength="255" autocomplete="off" value="{ps_pass}" placeholder="Пароль проекта">
				</div>
				<div id="edit_paysera_result"></div>

				<div class="row mt-10">
					<div class="col-md-6">
						<b> Тестовый режим </b>
						<div class="form-group">
							<div class="btn-group" data-toggle="buttons">
								<label class="btn btn-default {pstest}" onclick="change_value('config__bank','ps_test','1','1');">
									<input type="radio">
									Включить
								</label>

								<label class="btn btn-default {pstest2}" onclick="change_value('config__bank','ps_test','0','1');">
									<input type="radio">
									Выключить
								</label>
							</div>
						</div>
					</div>
					<div class="col-md-6">
						<b> Валюта </b>
						<div class="form-group">
							<div class="btn-group" data-toggle="buttons">
								<label class="btn btn-default {psRUB}" onclick="change_value('config__bank','ps_currency','RUB','1');">
									<input type="radio">
									RUB
								</label>

								<label class="btn btn-default {psUSD}" onclick="change_value('config__bank','ps_currency','USD','1');">
									<input type="radio">
									USD
								</label>

								<label class="btn btn-default {psEUR}" onclick="change_value('config__bank','ps_currency','EUR','1');">
									<input type="radio">
									EUR
								</label>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					LiqPay
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons" id="liqpayTrigger">
						<label class="btn btn-default {lpact}" onclick="change_value('config__bank','lp','1','1');">
							<input type="radio">
							Включить
						</label>
						<label class="btn btn-default {lpact2}" onclick="change_value('config__bank','lp','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="editLiqPayPaymentSystem();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="lp_public_key" maxlength="255" autocomplete="off" value="{lp_public_key}" placeholder="Публичный ключ">
					<input type="text" class="form-control" id="lp_private_key" maxlength="255" autocomplete="off" value="{lp_private_key}" placeholder="Секретный ключ">
				</div>
				<div id="edit_liqpay_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<table>
						<tr>
							<td colspan="2">Необходимые данные:</td>
						</tr>
						<tr>
							<td style="text-align: right">URL скрипта: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result_lp=get</b></td>
						</tr>
					</table>
				</div>
			</div>
		</div>


		<!--[ Payeer, добавил Metal Messiah ]-->
		<div class="block">
			<div class="block_head">
				Payeer
				<?if(time() < strtotime('2022-04-05')):?>
				<span class="badge">Новое</span>
				<?endif;?>
			</div>
				
			<div class="form-group mb-10">
				<div class="btn-group" data-toggle="buttons">
					<label class="btn btn-default {payeer_act}" onclick="change_value('config__bank','payeer','1','1','payeer_id,payeer_secret');">
						<input type="radio"> Включить
					</label>
					
					<label class="btn btn-default {payeer_act2}" onclick="change_value('config__bank','payeer','2','1');">
						<input type="radio"> Выключить
					</label>
				</div>
			</div>
				
			<div class="input-group">
				<span class="input-group-btn">
					<button class="btn btn-default pd-23-12" type="button" onclick="edit_payeer();">Изменить</button>
				</span>
				<input type="text" class="form-control" id="payeer_id" maxlength="255" autocomplete="off" value="{payeer_id}" placeholder="ID магазина">
				<input type="text" class="form-control" id="payeer_secret" maxlength="255" autocomplete="off" value="{payeer_secret}" placeholder="Секретный ключ">
			</div>
			<div id="edit_payeer_result"></div>
			<div class="bs-callout bs-callout-info mt-10">
				<p>
					<table>
					<tr>
						<td colspan="2">Необходимые данные:</td>
					</tr>
					<tr>
						<td style="text-align: right">URL оповещ: </td>
						<td>&nbsp&nbsp<b>{full_site_host}purse?payeer=get</b></td>
					</tr>
					<tr>
						<td style="text-align: right">URL успеха: </td>
						<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
					</tr>
					<tr>
						<td style="text-align: right">URL неуспеха: </td>
						<td>&nbsp&nbsp<b>{full_site_host}purse?result=fail</b></td>
					</tr>
				</table>
				</p>
			</div>
		</div>

		<!--[ UnitOay ]-->
		<div class="col-md-6">
			<div class="block">
				<div class="block_head">
					UnitPay
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {upact}" onclick="change_value('config__bank','up','1','1','up_type,up_pass1,up_pass2');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {upact2}" onclick="change_value('config__bank','up','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-40-12" type="button" onclick="edit_unitpay();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="up_pass1" maxlength="255" autocomplete="off" value="{up_pass1}" placeholder="PUBLIC KEY">
					<input type="text" class="form-control" id="up_pass2" maxlength="255" autocomplete="off" value="{up_pass2}" placeholder="SECRET KEY">
					<select class="form-control" id="up_type">
						<option value="1" {if('{up_type}' == '1')} selected {/if}>
							Способ работы: физ. лицо
						</option>
						<option value="2" {if('{up_type}' == '2')} selected {/if}>
							Способ работы: самозанятый / ИП / юр. лицо
						</option>
					</select>
				</div>
				<div id="edit_unitpay_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<p>
						<table>
							<tr>
								<td colspan="2">Необходимые данные:</td>
							</tr>
							<tr>
								<td style="text-align: right">Result Url: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse</b></td>
							</tr>
							<tr>
								<td style="text-align: right">Success Url: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
							</tr>
							<tr>
								<td style="text-align: right">Fail Url: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result=fail</b></td>
							</tr>
						</table>
					</p>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					WalletOne
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {woact}" onclick="change_value('config__bank','wo','1','1','wo_login,wo_pass');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {woact2}" onclick="change_value('config__bank','wo','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="edit_walletone();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="wo_login" maxlength="255" autocomplete="off" value="{wo_login}" placeholder="ID кассы">
					<input type="text" class="form-control" id="wo_pass" maxlength="255" autocomplete="off" value="{wo_pass}" placeholder="Секретный ключ">
				</div>
				<div id="edit_walletone_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<p>
						<table>
							<tr>
								<td colspan="2">Необходимые данные:</td>
							</tr>
							<tr>
								<td style="text-align: right">URL скрипта: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result_wo=get</b></td>
							</tr>
							<tr>
								<td style="text-align: right">Цифровая подпись: </td>
								<td>&nbsp&nbsp<b>MD5</b></td>
							</tr>
						</table>
					</p>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					WebMoney
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {wact}" onclick="change_value('config__bank','wb','1','1','wb_login,wb_pass1,wb_num');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {wact2}" onclick="change_value('config__bank','wb','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-40-12" type="button" onclick="edit_webmoney();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="wb_login" maxlength="255" autocomplete="off" value="{wb_login}" placeholder="Наименование">
					<input type="text" class="form-control" id="wb_pass1" maxlength="255" autocomplete="off" value="{wb_pass1}" placeholder="Пароль">
					<input type="text" class="form-control" id="wb_num" maxlength="255" autocomplete="off" value="{wb_num}" placeholder="Ваш кошелек">
				</div>
				<div id="edit_webmoney_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<p>
						<table>
							<tr>
								<td colspan="2">Необходимые данные:</td>
							</tr>
							<tr>
								<td style="text-align: right">Result Url: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result_wb=get</b></td>
							</tr>
							<tr>
								<td style="text-align: right">Success Url: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
							</tr>
							<tr>
								<td style="text-align: right">Fail Url: </td>
								<td>&nbsp&nbsp<b>{full_site_host}purse?result=fail</b></td>
							</tr>
						</table>
					</p>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					ЮMoney
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-default {yaact}" onclick="change_value('config__bank','ya','1','1','ya_num,ya_key');">
							<input type="radio">
							Включить
						</label>

						<label class="btn btn-default {yaact2}" onclick="change_value('config__bank','ya','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="edit_yandexmoney();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="ya_num" maxlength="255" autocomplete="off" value="{ya_num}" placeholder="Ваш кошелек">
					<input type="text" class="form-control" id="ya_key" maxlength="255" autocomplete="off" value="{ya_key}" placeholder="Секретный ключ">
				</div>
				<div id="edit_yandexmoney_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<p>Адрес для уведомлений: <b>{full_site_host}purse?result_ya=get</b></p>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					Qiwi
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons" id="qiwiTrigger">
						<label class="btn btn-default {qwact}" onclick="onQiwiPaymentSystem();">
							<input type="radio">
							Включить
						</label>
						<label class="btn btn-default {qwact2}" onclick="change_value('config__bank','qw','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" onclick="editQiwiPaymentSystem();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="qw_pass" maxlength="300" autocomplete="off" value="{qw_pass}" placeholder="Секретный ключ">
				</div>
				<div id="edit_qiwi_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<p>Адрес для серверных уведомлений: <b>{full_site_host}purse?result_qw=get</b></p>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					AnyPay
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons" id="liqpayTrigger">
						<label class="btn btn-default {apact}" onclick="change_value('config__bank','ap','1','1');">
							<input type="radio">
							Включить
						</label>
						<label class="btn btn-default {apact2}" onclick="change_value('config__bank','ap','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-23-12" type="button" onclick="editAnyPayPaymentSystem();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="ap_project_id" maxlength="255" autocomplete="off" value="{ap_project_id}" placeholder="ID проекта">
					<input type="text" class="form-control" id="ap_private_key" maxlength="255" autocomplete="off" value="{ap_private_key}" placeholder="Секретный ключ">
				</div>
				<div id="edit_anypay_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<table>
						<tr>
							<td colspan="2">Необходимые данные:</td>
						</tr>
						<tr>
							<td style="text-align: right">URL оповещения: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result_ap=get</b></td>
						</tr>
						<tr>
							<td style="text-align: right">URL успешной оплаты: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
						</tr>
						<tr>
							<td style="text-align: right">URL неуспешной оплаты: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result=fail</b></td>
						</tr>
					</table>
				</div>
			</div>

			<div class="block">
				<div class="block_head">
					Enot
				</div>
				<div class="form-group mb-10">
					<div class="btn-group" data-toggle="buttons" id="liqpayTrigger">
						<label class="btn btn-default {enotact}" onclick="change_value('config__bank','enot','1','1');">
							<input type="radio">
							Включить
						</label>
						<label class="btn btn-default {enotact2}" onclick="change_value('config__bank','enot','2','1');">
							<input type="radio">
							Выключить
						</label>
					</div>
				</div>
				<div class="input-group">
					<span class="input-group-btn">
						<button class="btn btn-default pd-40-12" type="button" onclick="editEnotPaymentSystem();">Изменить</button>
					</span>
					<input type="text" class="form-control" id="enot_id" maxlength="255" autocomplete="off" value="{enot_id}" placeholder="ID проекта">
					<input type="text" class="form-control" id="enot_key" maxlength="255" autocomplete="off" value="{enot_key}" placeholder="Секретный ключ">
					<input type="text" class="form-control" id="enot_key2" maxlength="255" autocomplete="off" value="{enot_key2}" placeholder="Секретный ключ 2">
				</div>
				<div id="edit_enot_result"></div>
				<div class="bs-callout bs-callout-info mt-10">
					<table>
						<tr>
							<td colspan="2">Необходимые данные:</td>
						</tr>
						<tr>
							<td style="text-align: right">URL оповещения: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?enot=pay</b></td>
						</tr>
						<tr>
							<td style="text-align: right">URL успешной оплаты: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result=success</b></td>
						</tr>
						<tr>
							<td style="text-align: right">URL неуспешной оплаты: </td>
							<td>&nbsp&nbsp<b>{full_site_host}purse?result=fail</b></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>