window.App = {};

App.q = function q(query) {
	var nodes = document.querySelectorAll(query);
	if (nodes.length === 1) {
		return nodes[0];
	}
	return nodes;
}

App.toggleClass = function toggleClass(element, className) {
	if (element.className.match(new RegExp(className))) {
		element.className = element.className.replace(' ' + className, '');
	} else {
		element.className += ' ' + className;
	}
}