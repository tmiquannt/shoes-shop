(() => {
    const init = () => {
        const hero = document.querySelector('.vyre-hero');
        if (!hero || hero.dataset.vyreSlideshowInitialized) return;

        const slides = [...hero.querySelectorAll('.vyre-hero-slide')];
        const controls = hero.querySelector('[data-vyre-progress]');
        const buttons = [...hero.querySelectorAll('[data-vyre-slide-to]')];
        const toggle = hero.querySelector('[data-vyre-toggle]');
        const indicator = hero.querySelector('[data-vyre-indicator]');
        const caption = hero.querySelector('[data-vyre-caption]');
        if (slides.length < 2 || buttons.length !== slides.length || !controls || !toggle) return;

        hero.dataset.vyreSlideshowInitialized = 'true';
        let current = Math.max(0, slides.findIndex(slide => slide.classList.contains('is-active')));
        let timer = null;
        let isPaused = Boolean(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);

        const isReady = index => {
            const image = slides[index].querySelector('img');
            return Boolean(image && image.complete && image.naturalWidth);
        };

        const clearTimer = () => {
            if (timer !== null) window.clearInterval(timer);
            timer = null;
        };

        const update = () => {
            slides.forEach((slide, index) => {
                slide.classList.toggle('is-active', index === current);
            });
            buttons.forEach((button, index) => {
                button.classList.toggle('is-active', index === current);
                if (index === current) button.setAttribute('aria-current', 'true');
                else button.removeAttribute('aria-current');
            });

            if (indicator) {
                indicator.textContent = `${String(current + 1).padStart(2, '0')} / ${String(slides.length).padStart(2, '0')}`;
            }
            if (caption) caption.textContent = slides[current].dataset.slideName || '';

            toggle.setAttribute(
                'aria-label',
                isPaused ? (toggle.dataset.labelPlay || 'Play slideshow') : (toggle.dataset.labelPause || 'Pause slideshow')
            );
            const icon = toggle.querySelector('[data-vyre-toggle-icon]');
            const label = toggle.querySelector('[data-vyre-toggle-label]');
            if (icon) icon.textContent = isPaused ? '▶' : 'Ⅱ';
            if (label) {
                const pausedText = toggle.dataset.textPaused || 'SLIDESHOW PAUSED';
                const playingText = toggle.dataset.textPlaying || 'SLIDESHOW PLAYING';
                label.textContent = isPaused ? pausedText : playingText;
            }
        };

        const show = index => {
            if (!isReady(index)) return false;
            current = index;
            update();
            return true;
        };

        const showNextReady = () => {
            for (let step = 1; step < slides.length; step += 1) {
                const next = (current + step) % slides.length;
                if (isReady(next)) {
                    show(next);
                    return;
                }
            }
        };

        const startAutoplay = () => {
            clearTimer();
            if (isPaused || document.hidden) return;
            timer = window.setInterval(showNextReady, 3000);
        };

        toggle.addEventListener('click', () => {
            isPaused = !isPaused;
            update();
            startAutoplay();
        });

        buttons.forEach((button, index) => {
            button.addEventListener('click', () => {
                show(index);
                startAutoplay();
            });
        });

        slides.forEach((slide, index) => {
            const image = slide.querySelector('img');
            if (!image) return;
            image.addEventListener('error', () => {
                buttons[index].disabled = true;
                if (index === current) showNextReady();
            });
        });

        controls.hidden = false;
        toggle.hidden = false;
        update();
        startAutoplay();

        document.addEventListener('visibilitychange', startAutoplay);
        window.addEventListener('pagehide', clearTimer);
        window.addEventListener('pageshow', startAutoplay);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
})();
