!(function(App) {

	document.addEventListener('DOMContentLoaded', function() {

		var headerHeight;
		var patience = 10;
		var swapped = false;
		var original = App.q('header h1').innerHTML;

		window.onresize = function() {
			headerHeight = App.q('header').offsetHeight + App.q('nav').offsetHeight;
		}

		window.onscroll = function() {
			if (window.scrollY > headerHeight + patience && !swapped) {
				App.q('header h1').innerHTML = App.q('.container > h1').innerHTML;
				swapped = true;
			} else if (window.scrollY < headerHeight && swapped) {
				App.q('header h1').innerHTML = original;
				swapped = false;
			}
		}

		window.onresize();
	});

})(window.App);

