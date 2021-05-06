<div class="col-lg-9 order-is-first">
	<div class="block block-search">
		<div class="block_head">
			Друзья {login}
		</div>
		<div class="input-search">
			<i class="fas fa-search" onclick="search_friend({id})"></i>
			<input type="text" class="form-control" id="search_login" placeholder="Введите логин друга">
			<script> set_enter('#search_login', 'search_friend({id})'); </script>
		</div>
		<div class="tab-content">
			<div class="tab-pane fade show active" id="friends" role="tabpanel" aria-labelledby="friends-tab">
				<div class="loader"></div>
			</div>
			<div class="tab-pane fade" id="unfriends" role="tabpanel" aria-labelledby="unfriends-tab">
				<div class="loader"></div>
			</div>
			<div class="tab-pane fade" id="infriends" role="tabpanel" aria-labelledby="infriends-tab">
				<div class="loader"></div>
			</div>
		</div>
	</div>
</div>

<div class="col-lg-3 order-is-last">
	<div class="block">
		<div class="block_head">
			Списки
		</div>
		<div class="vertical-navigation">
			<ul class="nav">
				<li>
					<a class="active" id="friends-tab" data-toggle="tab" href="#friends" onclick="load_friends('{id}')">Друзья</a>
				</li>
				<li>
					<a id="unfriends-tab" data-toggle="tab" href="#unfriends" onclick="load_friend_requests('un')">Исходящие заявки</a>
				</li>
				<li>
					<a id="infriends-tab" data-toggle="tab" href="#infriends" onclick="load_friend_requests('in')">Входящие заявки <font id="col_infriends"></font></a>
				</li>
			</ul>
		</div>
	</div>

	{include file="/home/navigation.tpl"}
	{include file="/home/sidebar_secondary.tpl"}
</div>

<script>
	load_friends("{id}");
	load_col_infriends();
</script>