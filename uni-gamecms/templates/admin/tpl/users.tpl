<div class="page">
	<div class="col-md-12">
		<div class="block">
			<div class="block_head">
				Поиск
			</div>
			<div class="input-group">
				<span class="input-group-btn">
					<button onclick="admin_search_login({start})" class="btn btn-default" type="button">Найти</button>
				</span>
				<input type="text" class="form-control" id="search_login" name="search_login" placeholder="Введите логин пользователя">
				<span class="input-group-btn w-150px">
					<select id="groups" class="form-control" onchange="group_change();">
						{groups}
					</select>
				</span>
			</div>
		</div>
		<br>
		<div id="pagination1"><center>{pagination}</center></div>
		<div id="users" class="row">
			<center><img src="{site_host}templates/admin/img/loader.gif"></center>
		</div>
		<div id="pagination2"><center>{pagination}</center></div>
	</div>
</div>
<script> 
	set_enter('#search_login', 'admin_search_login({start})'); 
	admin_load_users("{start}");
	function group_change() {
		var group = $('#groups').val();
		location.href = 'users?group='+group+'&page=1';
	}
</script>