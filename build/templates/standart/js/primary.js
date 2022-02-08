$(function() {
	preimage("cover");
	$("#cover_btn").bind("click", function() {
		$("#cover").click();
	});

	$("#cover").bind("change", function() {
		if(this.files[0].size > 100000000) {
			alert("Изображение не должно привышать 1мб");
			return;
		}

		var form = new FormData;
		form.append("change_cover", "1");
		form.append("image", this.files[0]);

		send_post(get_url() + "ajax/actions_a.php", form, function(result) {
			if(result.alert == 'success') {
				var cover = document.getElementsByClassName("cover");
				cover[0].style.backgroundImage = "url('" + result.file + "')";
			}
			else {
				alert(result.message);
			}
		});
	});
});

function preimage(class_name = "") {
	const o = document.getElementsByClassName(class_name);

	Array.from(o).map((item) => {
		const img = new Image();
		img.src = item.dataset.src;

		img.onload = () => {
			return item.nodeName === "IMG" ?
				item.src = item.dataset.src :
				item.style.backgroundImage = `url('${item.dataset.src}')`;
		}
	});
}