$(document).ready(function(){
	if($("#nav").length) {
		$('#nav > [class="collapsible"] > a').on("click", function(e){
			if($(this).parent().has("ul")) {
				e.preventDefault();
			}
			if(!$(this).hasClass("open")) {
				$("#nav li ul").slideUp(350);
				$("#nav li a").removeClass("open");
				$(this).next("ul").slideDown(350);
				$(this).addClass("open");
			} else if($(this).hasClass("open")) {
				$(this).removeClass("open");
				$(this).next("ul").slideUp(350);
			}
		});
	}
});

//ios sroll z-index fix
$(document).ready(function(){
	$("body").append("<div id='hidden_modals'></div>");
});
function is_ios() {
	var userAgent = navigator.userAgent || navigator.vendor || window.opera;

	if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
		return true;
    } else {
		return false;
    }	
}
function move_modals() {
	if(is_ios() && $('#hidden_modals').length > 0) {
		$(".table-responsive td .modal").each(function () {
			$(this).clone().appendTo("#hidden_modals");
			$(this).remove();
		});
	}
}
if(is_ios()) {
	$(document).ajaxStop(function () {
		move_modals();
	});
	$(document).ready(function(){
		move_modals();
	});
}