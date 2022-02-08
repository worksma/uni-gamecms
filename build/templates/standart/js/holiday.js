$(function () {
	var d = function () {};
	$(document).delegate(".b-ball_bounce", "mouseenter", function () {
		b(this);
		m(this)
	}).delegate(".b-ball_bounce .b-ball__right", "mouseenter", function (i) {
		i.stopPropagation();
		b(this);
		m(this)
	});
	function h(i) {
		if ($.browser.msie) {
			return window[i]
		} else {
			return document[i]
		}
	}
	var l = ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "-", "=", "q", "w", "e", "r", "t", "y", "u", "i", "o", "p", "[", "]", "a", "s", "d", "f", "g", "h", "j", "k", "l", ";", "'", "\\"];
	var k = ["z", "x", "c", "v", "b", "n", "m", ",", ".", "/"];
	var g = 36;
	var a = {};
	for (var e = 0, c = l.length; e < c; e++) {
		a[l[e].charCodeAt(0)] = e
	}
	for (var e = 0, c = k.length; e < c; e++) {
		a[k[e].charCodeAt(0)] = e
	}
	$(document).keypress(function (j) {
		var i = $(j.target);
		if (!i.is("input") && j.which in a) {
			d(a[j.which])
		}
	});
	function b(n) {
		if (n.className.indexOf("b-ball__right") > -1) {
			n = n.parentNode
		}
		var i = /b-ball_n(\d+)/.exec(n.className);
		var j = /b-head-decor__inner_n(\d+)/.exec(n.parentNode.className);
		if (i && j) {
			i = parseInt(i[1], 10) - 1;
			j = parseInt(j[1], 10) - 1;
			d((i + j * 9) % g)
		}
	}
	function m(j) {
		var i = $(j);
		if (j.className.indexOf(" bounce") > -1) {
			return
		}
		i.addClass("bounce");

		function n() {
			i.removeClass("bounce").addClass("bounce1");

			function o() {
				i.removeClass("bounce1").addClass("bounce2");

				function p() {
					i.removeClass("bounce2").addClass("bounce3");

					function q() {
						i.removeClass("bounce3")
					}
					setTimeout(q, 300)
				}
				setTimeout(p, 300)
			}
			setTimeout(o, 300)
		}
		setTimeout(n, 300)
	}
});