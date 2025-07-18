document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.has-submenu > a').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      e.preventDefault(); // Prevent link navigation
      const parent = anchor.parentElement;

      // Close other open menus
      document.querySelectorAll('.has-submenu.open').forEach(function (item) {
        if (item !== parent) item.classList.remove('open');
      });

      // Toggle current menu
      parent.classList.toggle('open');
    });
  });

  // Optional: close menu when clicking outside
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.main-menu')) {
      document.querySelectorAll('.has-submenu.open').forEach(function (item) {
        item.classList.remove('open');
      });
    }
  });
});S