(function () {
  'use strict';
  var tabs = Array.prototype.slice.call(document.querySelectorAll('[data-hatnch-tab]'));
  var panes = Array.prototype.slice.call(document.querySelectorAll('[data-hatnch-pane]'));
  if (!tabs.length || !panes.length) return;
  var activeInput = document.querySelector('[data-hatnch-active-tab]');
  var params = new URLSearchParams(window.location.search);
  var requested = params.get('tab');
  var allowed = tabs.map(function (tab) { return tab.getAttribute('data-hatnch-tab'); });
  function activate(key, moveFocus) {
    if (allowed.indexOf(key) === -1) key = allowed[0];
    tabs.forEach(function (tab) {
      var active = tab.getAttribute('data-hatnch-tab') === key;
      tab.classList.toggle('is-active', active);
      tab.setAttribute('aria-selected', active ? 'true' : 'false');
      tab.tabIndex = active ? 0 : -1;
      if (active && moveFocus) tab.focus();
    });
    panes.forEach(function (pane) {
      var active = pane.getAttribute('data-hatnch-pane') === key;
      pane.hidden = !active;
      pane.classList.toggle('is-active', active);
    });
    if (activeInput) activeInput.value = key;
  }
  tabs.forEach(function (tab, index) {
    tab.addEventListener('click', function () { activate(tab.getAttribute('data-hatnch-tab'), false); });
    tab.addEventListener('keydown', function (event) {
      var next = index;
      if (event.key === 'ArrowRight' || event.key === 'ArrowDown') next = (index + 1) % tabs.length;
      else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') next = (index - 1 + tabs.length) % tabs.length;
      else if (event.key === 'Home') next = 0;
      else if (event.key === 'End') next = tabs.length - 1;
      else return;
      event.preventDefault(); activate(tabs[next].getAttribute('data-hatnch-tab'), true);
    });
  });
  activate(requested || allowed[0], false);
}());
