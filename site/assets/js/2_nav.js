!(function(App) {
	document.addEventListener('DOMContentLoaded', function() {
		App.q('header button').addEventListener('click', function() {
			App.toggleClass(App.q('nav'), 'on');
		});
	});
})(window.App);
