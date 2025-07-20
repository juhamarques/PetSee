(function() {
  "use strict";

  /**
   * Apply .scrolled class to the body as the page is scrolled down
   */
  function toggleScrolled() {
    const selectBody = document.querySelector('body');
    const selectHeader = document.querySelector('#header');
    if (!selectHeader.classList.contains('scroll-up-sticky') && !selectHeader.classList.contains('sticky-top') && !selectHeader.classList.contains('fixed-top')) return;
    window.scrollY > 100 ? selectBody.classList.add('scrolled') : selectBody.classList.remove('scrolled');
  }

  document.addEventListener('scroll', toggleScrolled);
  window.addEventListener('load', toggleScrolled);

  /**
   * Mobile nav toggle
   */
  const mobileNavToggleBtn = document.querySelector('.mobile-nav-toggle');

  function mobileNavToogle() {
    document.querySelector('body').classList.toggle('mobile-nav-active');
    mobileNavToggleBtn.classList.toggle('bi-list');
    mobileNavToggleBtn.classList.toggle('bi-x');
  }
  if (mobileNavToggleBtn) {
    mobileNavToggleBtn.addEventListener('click', mobileNavToogle);
  }

  /**
   * Hide mobile nav on same-page/hash links
   */
  document.querySelectorAll('#navmenu a').forEach(navmenu => {
    navmenu.addEventListener('click', () => {
      if (document.querySelector('.mobile-nav-active')) {
        mobileNavToogle();
      }
    });

  });

  /**
   * Toggle mobile nav dropdowns
   * IMPORTANTE: 'toggle-dropdown' deve estar na tag <a> do item 'Cuidados' no HTML
   */
  document.querySelectorAll('.navmenu .toggle-dropdown').forEach(navmenu => {
    navmenu.addEventListener('click', function(e) {
      e.preventDefault();
      // 'this' aqui é o <a>. parentNode é o <li>.
      // Adiciona/remove a classe 'active' ao <li>, o que pode ser útil para estilos de dropdown
      this.parentNode.classList.toggle('active'); 
      // Abre/fecha o <ul> que é o próximo irmão do <li> pai
      this.parentNode.querySelector('ul').classList.toggle('dropdown-active'); // Corrigido para buscar o ul dentro do li
      e.stopImmediatePropagation();
    });
  });

  /**
   * Preloader
   */
  const preloader = document.querySelector('#preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      preloader.remove();
    });
  }

  /**
   * Scroll top button
   */
  let scrollTop = document.querySelector('.scroll-top');

  function toggleScrollTop() {
    if (scrollTop) {
      window.scrollY > 100 ? scrollTop.classList.add('active') : scrollTop.classList.remove('active');
    }
  }
  scrollTop.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  window.addEventListener('load', toggleScrollTop);
  document.addEventListener('scroll', toggleScrollTop);

  /**
   * Animation on scroll function and init
   */
  function aosInit() {
    AOS.init({
      duration: 600,
      easing: 'ease-in-out',
      once: true,
      mirror: false
    });
  }
  window.addEventListener('load', aosInit);

  /**
   * Init swiper sliders
   */
  function initSwiper() {
    document.querySelectorAll(".init-swiper").forEach(function(swiperElement) {
      let config = JSON.parse(
        swiperElement.querySelector(".swiper-config").innerHTML.trim()
      );

      if (swiperElement.classList.contains("swiper-tab")) {
        initSwiperWithCustomPagination(swiperElement, config);
      } else {
        new Swiper(swiperElement, config);
      }
    });
  }

  window.addEventListener("load", initSwiper);

  /**
   * Initiate glightbox
   */
  const glightbox = GLightbox({
    selector: '.glightbox'
  });

  /**
   * Init isotope layout and filters
   */
  document.querySelectorAll('.isotope-layout').forEach(function(isotopeItem) {
    let layout = isotopeItem.getAttribute('data-layout') ?? 'masonry';
    let filter = isotopeItem.getAttribute('data-default-filter') ?? '*';
    let sort = isotopeItem.getAttribute('data-sort') ?? 'original-order';

    let initIsotope;
    imagesLoaded(isotopeItem.querySelector('.isotope-container'), function() {
      initIsotope = new Isotope(isotopeItem.querySelector('.isotope-container'), {
        itemSelector: '.isotope-item',
        layoutMode: layout,
        filter: filter,
        sortBy: sort
      });
    });

    isotopeItem.querySelectorAll('.isotope-filters li').forEach(function(filters) {
      filters.addEventListener('click', function() {
        isotopeItem.querySelector('.isotope-filters .filter-active').classList.remove('filter-active');
        this.classList.add('filter-active');
        initIsotope.arrange({
          filter: this.getAttribute('data-filter')
        });
        if (typeof aosInit === 'function') {
          aosInit();
        }
      }, false);
    });

  });

  /**
   * Frequently Asked Questions Toggle
   */
  document.querySelectorAll('.faq-item h3, .faq-item .faq-toggle, .faq-item .faq-header').forEach((faqItem) => {
    faqItem.addEventListener('click', () => {
      faqItem.parentNode.classList.toggle('faq-active');
    });
  });

  /**
   * Correct scrolling position upon page load for URLs containing hash links.
   */
  window.addEventListener('load', function(e) {
    if (window.location.hash) {
      if (document.querySelector(window.location.hash)) {
        setTimeout(() => {
          let section = document.querySelector(window.location.hash);
          let scrollMarginTop = getComputedStyle(section).scrollMarginTop;
          window.scrollTo({
            top: section.offsetTop - parseInt(scrollMarginTop),
            behavior: 'smooth'
          });
        }, 100);
      }
    }
  });

  function activateNavMenuLinks() {
    const currentPagePathname = window.location.pathname; 
    document.querySelectorAll('.navmenu a.active').forEach(link => link.classList.remove('active'));

    const cuidadosDropdownParentLink = document.querySelector('.navmenu ul li.dropdown > a');
    
    const cuidadosPages = [
        '/cachorros.html',
        '/gatos.html',
        '/passaros.html',
        '/coelhos.html',
        '/peixes.html',
        '/roedores.html',
    ];

    let isActiveCuidadosPage = false;
    for (let i = 0; i < cuidadosPages.length; i++) {
        if (currentPagePathname.endsWith(cuidadosPages[i])) {
            isActiveCuidadosPage = true;
            break;
        }
    }

    if (cuidadosDropdownParentLink && isActiveCuidadosPage) {
        cuidadosDropdownParentLink.classList.add('active');
        return;
    }

    document.querySelectorAll('.navmenu a').forEach(navmenulink => {
        if (navmenulink.closest('.dropdown') && navmenulink !== cuidadosDropdownParentLink) {
             return;
        }

        const linkPathname = new URL(navmenulink.href).pathname;
        let normalizedCurrentPathname = currentPagePathname;

        if (normalizedCurrentPathname === '/') {
            normalizedCurrentPathname = '/index.html';
        }
        if (linkPathname === '/') {
            linkPathname = '/index.html';
        }

        if (linkPathname === normalizedCurrentPathname) {
            navmenulink.classList.add('active');
        }
    });
  }

  window.addEventListener('load', activateNavMenuLinks);

  /**
   * Navmenu Scrollspy 
   */
  let navmenulinks = document.querySelectorAll('.navmenu a');

  function navmenuScrollspy() {
    const cuidadosDropdownParentLink = document.querySelector('.navmenu ul li.dropdown > a');
    const cuidadosIsCurrentlyActive = cuidadosDropdownParentLink && cuidadosDropdownParentLink.classList.contains('active');

    navmenulinks.forEach(navmenulink => {
      if (!navmenulink.hash) {
          return;
      }

      if (navmenulink === cuidadosDropdownParentLink && cuidadosIsCurrentlyActive) {
          return;
      }

      let section = document.querySelector(navmenulink.hash);
      if (!section) return;

      let position = window.scrollY + 200; 

      if (position >= section.offsetTop && position <= (section.offsetTop + section.offsetHeight)) {
        if (!cuidadosIsCurrentlyActive) {
            document.querySelectorAll('.navmenu a.active').forEach(link => {
                if (link.hash) { 
                    link.classList.remove('active');
                }
            });
            navmenulink.classList.add('active');
        }
      } else {
        if (navmenulink !== cuidadosDropdownParentLink && navmenulink.hash) { 
             navmenulink.classList.remove('active');
        }
      }
    });
  }
  window.addEventListener('load', navmenuScrollspy);
  document.addEventListener('scroll', navmenuScrollspy);

})();