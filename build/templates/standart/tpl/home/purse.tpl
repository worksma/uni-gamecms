<div class="col-lg-9 order-is-first">

    {if('{success}' == '1')}
		<div class="noty-block success">
			<h4>Ваш баланс успешно пополнен!</h4>
			<p><b>{login}</b>, Ваш баланс успешно пополнен!</p>
		</div>
    {/if}
    {if('{fail}' == '1')}
		<div class="noty-block error">
			<h4>Внимание! Ваш баланс не пополнен!</h4>
			<p>Возможно произошла ошибка, либо Вы отменили платеж.</p>
		</div>
    {/if}

	<div class="block purse">
		<div class="row">
			<div class="col-lg-3">
				<div>
					<i class="fas fa-ruble-sign"></i>
					Баланс<br>
					<font id="my_balance">{balance} </font> {{sys()->currency()->lang}}
				</div>
			</div>
			<div class="col-lg-3">
				<div data-target="#voucher" data-toggle="modal">
					<i class="fas fa-barcode"></i>
					Активировать<br>
					ваучер
				</div>
			</div>
			<div class="col-lg-3">
				<div>
					<i class="fas fa-star"></i>
					Скидка<br>
                    {proc}%
				</div>
			</div>
			<div class="col-lg-3 d-none d-lg-block" onclick="scrollToBox('#last_operations');">
				<div>
					<i class="fas fa-shopping-cart"></i>
					Последние<br>
					операции
				</div>
			</div>
		</div>
	</div>

	{if('{bonusesActivity}' == '1')}
		<div class="block">
			<div class="block_head">
				Бонусы
			</div>

			<div class="row">
				{for($i = 0; $i < count($bonuses); $i++)}
					<div class="col-lg-{if(count($bonuses) % 4 == 0)}3{else}{if(count($bonuses) % 3 == 0)}4{else}{if(count($bonuses) % 2 == 0)}6{else}12{/if}{/if}{/if}">
						<div class="noty-block">
							<h5>+{{$bonuses[$i]['value']}}{if($bonuses[$i]['type'] == 1)} {{sys()->currency()->lang}}{else}%{/if}</h5>
							При пополнении на сумму от {{$bonuses[$i]['start']}} до {{$bonuses[$i]['end']}} {{sys()->currency()->lang}}
						</div>
					</div>
				{/for}
			</div>
		</div>
	{/if}

	<div class="row pay_area">
		{if('{up}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						UnitPay - VISA/MasterCard, ЮMoney, QIWI, SMS...
					</div>
					<label for="number_up">
						<img src="../templates/{template}/img/unitpay.jpg" alt="UnitPay">
					</label>
					<input class="form-control" id="number_up" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_up" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('up');">Пополнить баланс</button>
				</div>
			</div>
		{/if}
		
		{if('{payeer}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						Payeer - ваш электронный кошелек
					</div>
					<label for="number_up">
						<img src="../templates/{template}/img/payeer.jpg" alt="Payeer">
					</label>
					<input class="form-control" id="number_payeer" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_payeer" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('payeer');">Пополнить баланс</button>
				</div>
			</div>
		{/if}
		
		{if('{perfectmoney}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						PerfectMoney - надежный финансовый сервис
					</div>
					<label for="number_up">
						<img src="../templates/{template}/img/perfectmoney.jpg" alt="PerfectMoney">
					</label>
					<input class="form-control" id="number_perfectmoney" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_perfectmoney" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('perfectmoney');">Пополнить баланс</button>
				</div>
			</div>
		{/if}

		{if('{enot}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						Enot - VISA/MasterCard, ЮMoney, QIWI, SMS...
					</div>
					<label for="number_up">
						<img src="../templates/{template}/img/enot.jpg" alt="Enot">
					</label>
					<input class="form-control" id="number_enot" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_enot" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('enot');">Пополнить баланс</button>
				</div>
			</div>
		{/if}

		{if('{fk_new}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						FreeKassa - VISA/MasterCard, ЮMoney, QIWI, SMS...
					</div>
					<label for="number_fk_new">
						<img src="../templates/{template}/img/freekassa.jpg" alt="FreeKassa">
					</label>
					<input class="form-control" id="number_fk_new" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_fk_new" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('fk_new');">Пополнить баланс</button>
				</div>
			</div>
		{/if}

        {if('{rb}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						RoboKassa - VISA/MasterCard, ЮMoney, QIWI, SMS...
					</div>
					<label for="number_rb">
						<img src="../templates/{template}/img/robokassa.jpg" alt="RoboKassa">
					</label>
					<input class="form-control" id="number_rb" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_rb" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('rb');">Пополнить баланс</button>
				</div>
			</div>
        {/if}

        {if('{wb}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						WebMoney
					</div>
					<label for="number_wb">
						<img src="../templates/{template}/img/webmoney.jpg" alt="WebMoney">
					</label>
					<input class="form-control" id="number_wb" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_wb" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('wb');">Пополнить баланс</button>
				</div>
			</div>
        {/if}

        {if('{lava}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						QIWI, Lava, Банковские карты
					</div>
					<label for="number_lava">
						<img src="../templates/{template}/img/qiwi.jpg" alt="QIWI">
					</label>
					<input class="form-control" id="number_lava" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_lava" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('lava');">Пополнить баланс</button>
				</div>
			</div>
        {/if}

        {if('{qw}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						QIWI, Банковские карты
					</div>
					<label for="number_qw">
						<img src="../templates/{template}/img/qiwi.jpg" alt="QIWI">
					</label>
					<input class="form-control" id="number_qw" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_qw" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('qw');">Пополнить баланс</button>
				</div>
			</div>
        {/if}

        {if('{ya}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						ЮMoney, Банковские карты
					</div>
					<label for="number_ya">
						<img src="../templates/{template}/img/yoomoney.jpg" alt="ЮMoney">
					</label>
					<input class="form-control" id="number_ya" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_ya" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('ya');">Пополнить баланс</button>
				</div>
			</div>
        {/if}

        {if('{ik}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						InterKassa - QIWI, ЮMoney, VISA/MasterCard, WebMoney...
					</div>
					<label for="number_ik">
						<img src="../templates/{template}/img/interkassa.jpg" alt="InterKassa">
					</label>
					<input class="form-control" id="number_ik" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_ik" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('ik');">Пополнить баланс</button>
				</div>
			</div>
        {/if}

        {if('{wo}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						WalletOne - QIWI, ЮMoney, VISA/MasterCard, WebMoney...
					</div>
					<label for="number_wo">
						<img src="../templates/{template}/img/walletone.jpg" alt="WalletOne">
					</label>
					<input class="form-control" id="number_wo" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_wo" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('wo');">Пополнить баланс</button>
				</div>
			</div>
        {/if}

        {if('{ps}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						Paysera
					</div>
					<label for="number_ps">
						<img src="../templates/{template}/img/paysera.jpg" alt="Paysera">
					</label>
					<input class="form-control" id="number_ps" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_ps" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('ps');">Пополнить баланс</button>
				</div>
			</div>
        {/if}

        {if('{lp}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						LiqPay - Mastercard/Visa, Приват24
					</div>
					<label for="number_ps">
						<img src="../templates/{template}/img/liqpay.jpg" alt="LiqPay">
					</label>
					<input class="form-control" id="number_lp" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_lp" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('lp');">Пополнить баланс</button>
				</div>
			</div>
        {/if}

        {if('{ap}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						AnyPay - VISA/MasterCard, ЮMoney, QIWI, SMS...
					</div>
					<label for="number_ps">
						<img src="../templates/{template}/img/anypay.jpg" alt="AnyPay">
					</label>
					<input class="form-control" id="number_ap" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_ap" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('ap');">Пополнить баланс</button>
				</div>
			</div>
        {/if}
		
		{if('{amara}' == '1')}
			<div class="col-md-6">
				<div class="block">
					<div class="block_head">
						AmaraPay - Visa/MasterCard, ЮMoney, Qiwi, SMS...
					</div>
					<label for="number_amarapay">
						<img src="../templates/{template}/img/amarapay.png" alt="AmaraPay">
					</label>
					<input class="form-control" id="number_amarapay" placeholder="Укажите сумму" value="{price}">
					<div id="balance_result_amarapay" class="mt-3"></div>
					<button class="btn btn-outline-primary btn-xl" onclick="refill_balance('amarapay');">Пополнить баланс</button>
				</div>
			</div>
		{/if}
	</div>

	<script>$('#voucher').modal('hide');</script>
	<div id="voucher" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h4 class="modal-title">Активация ваучера</h4>
				</div>
				<div class="modal-body">
					<input class="form-control" type="text" id="voucher_key" placeholder="Введите ваучер" maxlength="50">
					<button class="btn btn-primary" onclick="activate_voucher();">Активировать</button>
					<br>
					<div id="activate_result"></div>
				</div>
			</div>
		</div>
	</div>

	<div class="block block-table" id="last_operations">
		<div class="block_head">
			Последние операции
		</div>
		<div class="table-responsive mb-0">
			<table class="table table-bordered">
				<thead>
				<tr>
					<td>Сумма</td>
					<td>Тип</td>
					<td>Дата</td>
				</tr>
				</thead>
				<tbody id="operations">
				<tr>
					<td colspan="10">
						<div class="loader"></div>
						<script>get_operations();</script>
					</td>
				</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="col-lg-3 order-is-last">
    {include file="/home/navigation.tpl"}
    {include file="/home/sidebar.tpl"}
</div>