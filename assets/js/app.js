// LIFNOOK - small JavaScript helpers

document.addEventListener('DOMContentLoaded', function () {
  // 1. Auto-hide flash messages after 4 seconds
  document.querySelectorAll('.flash').forEach(function (box) {
    setTimeout(function () {
      box.style.opacity = '0';
      setTimeout(function () { box.remove(); }, 500);
    }, 4000);
  });

  // 2. Register form: check passwords match before submitting
  var reg = document.getElementById('registerForm');
  if (reg) {
    reg.addEventListener('submit', function (ev) {
      var pw = reg.querySelector('[name=password]').value;
      var cf = reg.querySelector('[name=confirm]').value;
      if (pw !== cf) {
        ev.preventDefault();
        alert('Passwords do not match. Please check again.');
      }
    });
  }

  // 3. Confetti when a new achievement is added
  if (window.LIFNOOK_CELEBRATE) {
    var colors = ['#7c6cf0', '#ff8fab', '#4cc9a0', '#ffd166', '#6ec6ff'];
    for (var i = 0; i < 80; i++) {
      var c = document.createElement('div');
      c.className = 'confetti';
      c.style.left = Math.random() * 100 + 'vw';
      c.style.background = colors[Math.floor(Math.random() * colors.length)];
      c.style.animationDuration = (2 + Math.random() * 2.5) + 's';
      c.style.animationDelay = (Math.random() * 0.8) + 's';
      document.body.appendChild(c);
      setTimeout(function (el) { el.remove(); }, 6000, c);
    }
  }
});
