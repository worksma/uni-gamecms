<div class="col-lg-12 order-is-first">
	<div class="row">
		<div class="col-md-6">
			У Вас на счету {balance}<br>
			<a href="#" data-toggle="modal" data-target="#exchanger">Пополнить счёт</a>
		</div>
		<div class="col-md-6 col-md-push-12 text-right">
			<a class="btn btn-default" href="/market">Торговая площадка</a>
		</div>
	</div>
	
	<div class="row" id="items">{content}</div>
</div>

<!--[ Обменник валюты ]!-->
<div class="modal fade" id="exchanger" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Обмен валюты</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						<input id="exchangerValue" type="number" min="1" class="form-control" placeholder="Сколько Вы хотите получить?">
						<div id="result_clc" class="text-center mb-4"></div>
					</div>
					<div class="col-md-6">
						Вы отдаёте: <font id="recoil" class="text-success">0</font><br>
						Получаете: <font id="receiving"  class="text-success">0</font>
					</div>
					<div class="col-md-6 col-md-push-12 text-right">
						<button class="btn btn-default" onclick="on_exchange();">Обменять</button>
						<button class="btn btn-default" onclick="clc();">Подсчитать</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>