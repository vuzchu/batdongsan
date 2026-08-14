document.addEventListener('DOMContentLoaded', function () {
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.querySelector('.main-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
    });
  }

  var tabs = document.querySelectorAll('.search-tabs button');
  var input = document.querySelector('input[name="transaction_type"]');
  tabs.forEach(function (btn) {
    btn.addEventListener('click', function () {
      tabs.forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      if (input) input.value = btn.dataset.value || '';
    });
  });

  var mainImage = document.getElementById('detailMainImage');
  var thumbs = document.querySelectorAll('.gallery-thumb');
  if (mainImage && thumbs.length) {
    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        mainImage.src = thumb.src;
        thumbs.forEach(function (t) { t.classList.remove('active'); });
        thumb.classList.add('active');
      });
    });
  }
});
