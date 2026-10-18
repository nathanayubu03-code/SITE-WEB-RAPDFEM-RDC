(function () {
  'use strict';

  // Menu mobile
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('menu-principal');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = toggle.getAttribute('aria-expanded') === 'true';
      toggle.setAttribute('aria-expanded', String(!open));
      nav.classList.toggle('is-open', !open);
    });
  }

  // Filtres : envoi automatique au changement
  document.querySelectorAll('form[data-autosubmit] select').forEach(function (select) {
    select.addEventListener('change', function () { select.form.submit(); });
  });

  // Diaporama d'accueil
  var slider = document.querySelector('[data-slider]');
  if (slider) {
    var slides = slider.querySelectorAll('[data-slide]');
    var dots = slider.querySelectorAll('[data-dot]');
    var index = 0;
    var timer = null;
    var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var show = function (i) {
      index = (i + slides.length) % slides.length;
      slides.forEach(function (s, n) {
        s.classList.toggle('is-active', n === index);
        s.setAttribute('aria-hidden', n === index ? 'false' : 'true');
      });
      dots.forEach(function (d, n) { d.classList.toggle('is-active', n === index); });
    };
    var start = function () {
      if (reduced || slides.length < 2) return;
      stop();
      timer = window.setInterval(function () { show(index + 1); }, 6500);
    };
    var stop = function () { if (timer) window.clearInterval(timer); };

    var prev = slider.querySelector('[data-prev]');
    var next = slider.querySelector('[data-next]');
    if (prev) prev.addEventListener('click', function () { show(index - 1); start(); });
    if (next) next.addEventListener('click', function () { show(index + 1); start(); });
    dots.forEach(function (d) {
      d.addEventListener('click', function () { show(parseInt(d.getAttribute('data-dot'), 10)); start(); });
    });
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', start);
    start();
  }

  // Visionneuse photos et vidéos
  var items = Array.prototype.slice.call(document.querySelectorAll('[data-lightbox]'));
  if (!items.length) return;

  var box = document.createElement('div');
  box.className = 'lightbox';
  box.hidden = true;
  box.setAttribute('role', 'dialog');
  box.setAttribute('aria-modal', 'true');
  box.setAttribute('aria-label', 'Visionneuse');
  box.innerHTML =
    '<button type="button" class="lightbox__btn lightbox__close" aria-label="Fermer">&times;</button>' +
    '<button type="button" class="lightbox__btn lightbox__prev" aria-label="Précédent">&#8249;</button>' +
    '<div class="lightbox__stage"></div>' +
    '<button type="button" class="lightbox__btn lightbox__next" aria-label="Suivant">&#8250;</button>';
  document.body.appendChild(box);

  var stage = box.querySelector('.lightbox__stage');
  var current = 0;
  var lastFocus = null;

  var render = function () {
    var item = items[current];
    stage.textContent = '';
    var el;
    if (item.hasAttribute('data-video')) {
      el = document.createElement('iframe');
      el.src = item.getAttribute('href');
      el.title = item.getAttribute('data-caption') || 'Vidéo';
      el.allow = 'autoplay; encrypted-media; picture-in-picture';
      el.allowFullscreen = true;
    } else {
      el = document.createElement('img');
      el.src = item.getAttribute('href');
      el.alt = item.getAttribute('data-caption') || '';
    }
    stage.appendChild(el);
    var caption = item.getAttribute('data-caption');
    if (caption) {
      var p = document.createElement('p');
      p.className = 'lightbox__caption';
      p.textContent = caption + '  (' + (current + 1) + ' / ' + items.length + ')';
      stage.appendChild(p);
    }
  };
  var open = function (i) {
    current = i;
    lastFocus = document.activeElement;
    render();
    box.hidden = false;
    document.body.style.overflow = 'hidden';
    box.querySelector('.lightbox__close').focus();
  };
  var close = function () {
    box.hidden = true;
    stage.textContent = '';
    document.body.style.overflow = '';
    if (lastFocus) lastFocus.focus();
  };
  var move = function (d) { current = (current + d + items.length) % items.length; render(); };

  items.forEach(function (item, i) {
    item.addEventListener('click', function (e) { e.preventDefault(); open(i); });
  });
  box.querySelector('.lightbox__close').addEventListener('click', close);
  box.querySelector('.lightbox__prev').addEventListener('click', function () { move(-1); });
  box.querySelector('.lightbox__next').addEventListener('click', function () { move(1); });
  box.addEventListener('click', function (e) { if (e.target === box) close(); });
  document.addEventListener('keydown', function (e) {
    if (box.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') move(-1);
    if (e.key === 'ArrowRight') move(1);
  });
  if (items.length < 2) {
    box.querySelector('.lightbox__prev').hidden = true;
    box.querySelector('.lightbox__next').hidden = true;
  }
})();
