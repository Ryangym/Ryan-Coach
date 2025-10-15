const menuToggle = document.getElementById('menu-toggle');
const mobileNav = document.getElementById('mobile-nav');
const mobileNavbar = document.querySelector('.mobile-navbar');
const desktopNavbar = document.querySelector('.navbar');

let lastScrollTop = 0;

// Animação do botão hambúrguer
menuToggle?.addEventListener('click', () => {
  const isActive = mobileNav.classList.toggle('active');
  menuToggle.classList.toggle('active');
  document.body.classList.toggle('noscroll', isActive);
});

// Fecha o menu mobile ao clicar em qualquer link
const navLinks = mobileNav.querySelectorAll('a');

navLinks.forEach(link => {
  link.addEventListener('click', () => {
    mobileNav.classList.remove('active');
    menuToggle.classList.remove('active');
    document.body.classList.remove('noscroll');
  });
});

// Esconde/mostra navbar com scroll
window.addEventListener('scroll', () => {
  const scrollTop = window.scrollY || document.documentElement.scrollTop;

  // Esconde se rolar para baixo, mostra se rolar para cima
  const shouldHide = scrollTop > lastScrollTop;

  if (window.innerWidth > 768 && desktopNavbar) {
    desktopNavbar.classList.toggle('hidden', shouldHide);
  }

  if (window.innerWidth <= 768 && mobileNavbar && !mobileNav.classList.contains('active')) {
    mobileNavbar.classList.toggle('hidden', shouldHide);
  }

  lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
});
