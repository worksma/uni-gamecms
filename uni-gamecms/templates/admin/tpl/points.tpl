<div id="points_result"></div>
<div class="page">
	<div class="row">
		<div class="col-md-6">
			<div class="block">
				<div class="block_head">
					Управление категориями
				</div>
				
				<div class="col">
					<div class="row">
						<div class="col-lg-6">
							<b>Добавление категорий</b>
							<div class="input-group w-100">
								<span class="input-group-btn w-100">
									<input type="text" class="form-control" maxlength="15" autocomplete="off" placeholder="Наименование каталога" id="categoryName">
								</span>
							</div>
						</div>
						<div class="col-lg-6">
							<b>Кодовое слово</b>
							<div class="input-group w-100">
								<span class="input-group-btn w-100">
									<input type="text" class="form-control" maxlength="15" autocomplete="off" placeholder="none" id="categoryCode">
								</span>
							</div>
						</div>
					</div>
					<br>
					<button class="btn btn-sm btn-primary btn-block" OnClick="addCategoryPoints();">Добавить</button>
				</div>
				<hr>
				<b>Удаление категорий</b>
				<div class="input-group w-100">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button" OnClick="delCategoryPoints();">Удалить</button>
					</span>
					<span class="input-group-btn w-100">
						<select class="form-control" id="delIndexCategory">
							{points_category}
						</select>
					</span>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="block">
				<div class="block_head">
					Добавление товара
				</div>
				
				<b>Наименование товара</b>
				<input type="text" class="form-control" maxlength="15" autocomplete="off" placeholder="Название товара" id="prodName">
				
				<div class="col">
					<div class="row">
						<div class="col-lg-6">
							<b>Категория товара</b>
							<select class="form-control" id="prodCategory">
								{points_category}
							</select>
						</div>
						<div class="col-lg-6">
							<b>Цена</b>
							<input type="text" class="form-control" maxlength="15" autocomplete="off" placeholder="500" id="prodPrice">
						</div>
					</div>
				</div>
				
				<b>Ресурс</b>
				<div class="col">
					<div class="row">
						<div class="col-lg-12">
							<input type="file" class="form-control" accept="image/jpeg, image/png, image/gif" id="filePoints"><br>
							<button class="btn btn-sm btn-primary btn-block" OnClick="addProductPoints();">Добавить</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-md-12">
			<div class="block">
				<div class="block_head">
					Список товаров
				</div>
				
				<div id="point_list">
					{product_points}
				</div>
			</div>
		</div>
	</div>
</div>
<link href="{site_host}templates/admin/js/toasts/toasty.min.css" rel="stylesheet"/>
<script src="{site_host}templates/admin/js/toasts/toasty.min.js" type="text/javascript"></script>