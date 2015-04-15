!(function(App) {

	document.addEventListener('DOMContentLoaded', function() {

		var headerHeight = App.q('header').offsetHeight;
		var patience = 10;
		var swapped = false;
		var original = App.q('.title h1').innerHTML;

		window.onresize = function() {
			headerHeight = App.q('header').offsetHeight;
		}

		window.onscroll = function() {
			if (window.scrollY > headerHeight + patience && !swapped) {
				App.q('.title h1').innerHTML = App.q('.container > h1').innerHTML;
				swapped = true;
			} else if (window.scrollY < headerHeight && swapped) {
				App.q('.title h1').innerHTML = original;
				swapped = false;
			}
		}

		window.onresize();
	});

})(window.App);

