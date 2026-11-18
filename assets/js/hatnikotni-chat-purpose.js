(function () {
	'use strict';
	var openButton = document.querySelector('[data-hatnch-purpose-open]');
	var panel = document.getElementById('hatnch-purpose-panel');
	if (!openButton || !panel) return;
	var closeButton = panel.querySelector('[data-hatnch-purpose-close]');
	function closePanel() {
		panel.hidden = true;
		openButton.setAttribute('aria-expanded', 'false');
		openButton.focus();
	}
	openButton.addEventListener('click', function () {
		panel.hidden = !panel.hidden;
		openButton.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
		if (!panel.hidden && closeButton) closeButton.focus();
	});
	if (closeButton) closeButton.addEventListener('click', closePanel);
	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && !panel.hidden) closePanel();
	});
	document.addEventListener('click', function (event) {
		if (!panel.hidden && !panel.contains(event.target) && !openButton.contains(event.target)) closePanel();
	});
}());
