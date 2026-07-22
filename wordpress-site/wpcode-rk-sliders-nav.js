document.addEventListener('DOMContentLoaded', function () {
  function stepOf(track) {
    var card = track.querySelector('.e-con');
    var gap = parseFloat(getComputedStyle(track).gap) || 16;
    return card ? card.getBoundingClientRect().width + gap : track.clientWidth / 3;
  }
  document.querySelectorAll('[data-rk-slide]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var track = document.querySelector('.' + btn.getAttribute('data-rk-target'));
      if (!track) return;
      var dir = btn.getAttribute('data-rk-slide') === 'next' ? 1 : -1;
      track.scrollBy({ left: dir * stepOf(track), behavior: 'smooth' });
    });
  });
  var gal = document.querySelector('.rk-gallery-track');
  if (gal) {
    setInterval(function () {
      if (gal.matches(':hover')) return;
      if (gal.scrollLeft + gal.clientWidth >= gal.scrollWidth - 4) gal.scrollTo({ left: 0, behavior: 'smooth' });
      else gal.scrollBy({ left: stepOf(gal), behavior: 'smooth' });
    }, 3800);
  }
  document.querySelectorAll('.rk-nav-links a').forEach(function (a) {
    a.addEventListener('click', function () {
      var nl = document.querySelector('.rk-nav-links');
      if (nl) nl.classList.remove('open');
    });
  });
});