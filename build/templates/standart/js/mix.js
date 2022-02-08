!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof exports?module.exports=a:a(jQuery)}(function(a){function b(b){var g=b||window.event,h=i.call(arguments,1),j=0,l=0,m=0,n=0,o=0,p=0;if(b=a.event.fix(g),b.type="mousewheel","detail"in g&&(m=-1*g.detail),"wheelDelta"in g&&(m=g.wheelDelta),"wheelDeltaY"in g&&(m=g.wheelDeltaY),"wheelDeltaX"in g&&(l=-1*g.wheelDeltaX),"axis"in g&&g.axis===g.HORIZONTAL_AXIS&&(l=-1*m,m=0),j=0===m?l:m,"deltaY"in g&&(m=-1*g.deltaY,j=m),"deltaX"in g&&(l=g.deltaX,0===m&&(j=-1*l)),0!==m||0!==l){if(1===g.deltaMode){var q=a.data(this,"mousewheel-line-height");j*=q,m*=q,l*=q}else if(2===g.deltaMode){var r=a.data(this,"mousewheel-page-height");j*=r,m*=r,l*=r}if(n=Math.max(Math.abs(m),Math.abs(l)),(!f||f>n)&&(f=n,d(g,n)&&(f/=40)),d(g,n)&&(j/=40,l/=40,m/=40),j=Math[j>=1?"floor":"ceil"](j/f),l=Math[l>=1?"floor":"ceil"](l/f),m=Math[m>=1?"floor":"ceil"](m/f),k.settings.normalizeOffset&&this.getBoundingClientRect){var s=this.getBoundingClientRect();o=b.clientX-s.left,p=b.clientY-s.top}return b.deltaX=l,b.deltaY=m,b.deltaFactor=f,b.offsetX=o,b.offsetY=p,b.deltaMode=0,h.unshift(b,j,l,m),e&&clearTimeout(e),e=setTimeout(c,200),(a.event.dispatch||a.event.handle).apply(this,h)}}function c(){f=null}function d(a,b){return k.settings.adjustOldDeltas&&"mousewheel"===a.type&&b%120===0}var e,f,g=["wheel","mousewheel","DOMMouseScroll","MozMousePixelScroll"],h="onwheel"in document||document.documentMode>=9?["wheel"]:["mousewheel","DomMouseScroll","MozMousePixelScroll"],i=Array.prototype.slice;if(a.event.fixHooks)for(var j=g.length;j;)a.event.fixHooks[g[--j]]=a.event.mouseHooks;var k=a.event.special.mousewheel={version:"3.1.12",setup:function(){if(this.addEventListener)for(var c=h.length;c;)this.addEventListener(h[--c],b,!1);else this.onmousewheel=b;a.data(this,"mousewheel-line-height",k.getLineHeight(this)),a.data(this,"mousewheel-page-height",k.getPageHeight(this))},teardown:function(){if(this.removeEventListener)for(var c=h.length;c;)this.removeEventListener(h[--c],b,!1);else this.onmousewheel=null;a.removeData(this,"mousewheel-line-height"),a.removeData(this,"mousewheel-page-height")},getLineHeight:function(b){var c=a(b),d=c["offsetParent"in a.fn?"offsetParent":"parent"]();return d.length||(d=a("body")),parseInt(d.css("fontSize"),10)||parseInt(c.css("fontSize"),10)||16},getPageHeight:function(b){return a(b).height()},settings:{adjustOldDeltas:!0,normalizeOffset:!0}};a.fn.extend({mousewheel:function(a){return a?this.bind("mousewheel",a):this.trigger("mousewheel")},unmousewheel:function(a){return this.unbind("mousewheel",a)}})});

$(document).ready(function(){
	//monitoring scroll
	$('.monitoring').mousewheel(function(e,delta){
		if(!$(this).find('.modal.fade.show').length > 0) {
			this.scrollLeft-=(delta*40);
			e.preventDefault();
		}
	});

	//menu
	$('.collapsible-menu > [class="collapsible"] > a').on("click", function(e){
		if($(this).parent().has("ul")) {
			e.preventDefault();
		}

		if(!$(this).hasClass("open")) {
			$(this).next("ul").slideDown(350);
			$(this).addClass("open");
		} else if($(this).hasClass("open")) {
			$(this).removeClass("open");
			$(this).next("ul").slideUp(350);
		}

		$('.collapsible-menu > .collapsible').each(function(){
			$(this).find('ul').css("min-width", $(this).find('a').innerWidth()+"px");
		});
		$('.user-menu > .collapsible').each(function(){
			$(this).find('ul').width($(this).find('a').innerWidth());
		});
	});

	$(document).mouseup(function (e) {
		if ($('.collapsible-menu > [class="collapsible"]').has(e.target).length === 0 && $('.collapsible-menu > [class="collapsible"] > a').hasClass("open")) {
			$('.collapsible-menu > [class="collapsible"] > ul').parent().find("a").removeClass("open");
			$('.collapsible-menu > [class="collapsible"] > ul').slideUp(350);
		}
	});

	//spoiler
	$('div.spoiler-title').click(function() {
		$(this)
			.children()
			.first()
			.toggleClass('show-icon')
			.toggleClass('hide-icon');
		$(this)
			.parent().children().last().toggle(100);
	});
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

	$("#servers .modal").each(function () {
		$(this).clone().appendTo("#hidden_modals");
		$(this).remove();
	});
}
if(is_ios()) {
	$(document).ajaxStop(function () {
		move_modals();
	});
}
$(document).ready(function(){
	move_modals();
});