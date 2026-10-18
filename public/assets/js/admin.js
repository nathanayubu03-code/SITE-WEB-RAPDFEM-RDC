(function () {
  'use strict';

  // Menu latéral sur mobile
  var btn = document.querySelector('[data-toggle-sidebar]');
  var sidebar = document.getElementById('sidebar');
  if (btn && sidebar) {
    btn.addEventListener('click', function () {
      var open = sidebar.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', String(open));
    });
  }

  // Confirmation avant suppression
  document.querySelectorAll('form[data-confirm]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (!window.confirm(form.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // Empêche le double envoi des formulaires (téléversements lents)
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      if (e.defaultPrevented) return;
      if (form.dataset.sending) { e.preventDefault(); return; }
      form.dataset.sending = '1';
      var submit = form.querySelector('button[type=submit]') || document.querySelector('button[type=submit][form="' + form.id + '"]');
      if (submit && form.enctype === 'multipart/form-data') submit.textContent = 'Envoi en cours…';
    });
  });

  // QR code de configuration de la double authentification
  var qr = document.querySelector('[data-qr]');
  if (qr && typeof window.qrcode === 'function') {
    var code = window.qrcode(0, 'M');
    code.addData(qr.getAttribute('data-qr'));
    code.make();
    var img = document.createElement('img');
    img.src = code.createDataURL(6, 2);
    img.alt = 'QR code à scanner avec votre application d\'authentification';
    qr.appendChild(img);
  }
})();
