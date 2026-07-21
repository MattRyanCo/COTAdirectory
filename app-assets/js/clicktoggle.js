document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.has-submenu > a').forEach(function (anchor) {
    if (anchor.getAttribute('data-bs-toggle') === 'dropdown') {
      return;
    }

    anchor.addEventListener('click', function (e) {
      e.preventDefault();
      const parent = anchor.parentElement;

      document.querySelectorAll('.has-submenu.open').forEach(function (item) {
        if (item !== parent) item.classList.remove('open');
      });

      parent.classList.toggle('open');
    });
  });

  document.addEventListener('click', function (e) {
    if (!e.target.closest('.main-menu')) {
      document.querySelectorAll('.has-submenu.open').forEach(function (item) {
        item.classList.remove('open');
      });
    }
  });
});