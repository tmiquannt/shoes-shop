/* Image-only enhancement: the hero content never moves or gets re-announced. */
(() => {
 const init = () => {
  const hero = document.querySelector('.energy-hero');
  if (!hero || hero.dataset.autoplayInitialized) return;
  const slides = [...hero.querySelectorAll('.energy-hero-image')];
  const dots = [...hero.querySelectorAll('[data-hero-slide]')];
  const controls = hero.querySelector('.energy-hero-controls');
  const toggle = hero.querySelector('.energy-hero-toggle');
  if (slides.length < 2 || !controls || !toggle) return;
  hero.dataset.autoplayInitialized = 'true';
  let current = 0;
  let autoplayTimer = null;
  let isPausedByUser = false;
  const clearAutoplay = () => {
   if (autoplayTimer !== null) window.clearInterval(autoplayTimer);
   autoplayTimer = null;
  };
  const show = index => {
   const image = slides[index];
   if (!image.complete || !image.naturalWidth) return;
   slides[current].classList.remove('is-active');
   dots[current].removeAttribute('aria-current');
   current = index;
   image.classList.add('is-active');
   dots[index].setAttribute('aria-current', 'true');
  };
  const goToNextSlide = () => {
   for (let step = 1; step < slides.length; step++) {
    const next = (current + step) % slides.length;
    if (slides[next].complete && slides[next].naturalWidth) { show(next); break; }
   }
  };
  const startAutoplay = () => {
   clearAutoplay();
   if (isPausedByUser || document.hidden) return;
   autoplayTimer = window.setInterval(goToNextSlide, 5000);
  };
  const updateToggle = () => {
   toggle.setAttribute('aria-label', isPausedByUser ? 'Play slideshow' : 'Pause slideshow');
   toggle.firstElementChild.textContent = isPausedByUser ? '▶' : 'Ⅱ';
  };
  toggle.addEventListener('click', () => {
   isPausedByUser = !isPausedByUser;
   updateToggle();
   startAutoplay();
  });
  dots.forEach((dot, index) => dot.addEventListener('click', () => {
   show(index);
   startAutoplay();
  }));
  // Only explicit Pause persists. Pointer/focus never override the chosen state.
  // Reduced-motion CSS still removes fading, without disabling image rotation.
  document.addEventListener('visibilitychange', startAutoplay);
  window.addEventListener('pagehide', clearAutoplay);
  window.addEventListener('pageshow', startAutoplay);
  slides.forEach((slide, index) => slide.addEventListener('error', () => {
   dots[index].disabled = true;
  }));
  controls.hidden = false;
  hero.classList.add('has-slideshow');
  updateToggle();
  startAutoplay();
 };
 if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, {once:true});
 else init();
})();
