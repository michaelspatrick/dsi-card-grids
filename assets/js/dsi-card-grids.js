(function() {
    function initSlider(container) {
        var track = container.querySelector('.dsi-card-slider-track');
        if (!track) return;

        var cards = track.querySelectorAll('.dsi-card');
        if (!cards.length) return;

        var items = parseInt(container.getAttribute('data-dsi-items'), 10) || 3;
        var interval = parseInt(container.getAttribute('data-dsi-interval'), 10) || 5000;

        var currentIndex = 0;
        var total = cards.length;
        var autoplayTimer = null;

        // Determine card width percentage based on items
        var cardWidthPercent = (100 / items);
        cards.forEach(function(card) {
            card.style.flex = '0 0 ' + cardWidthPercent + '%';
            card.style.maxWidth = cardWidthPercent + '%';
        });

        function goTo(index) {
            if (index < 0) {
                index = Math.max(0, total - items);
            } else if (index > total - items) {
                index = 0;
            }
            currentIndex = index;
            var translateX = -(cardWidthPercent * currentIndex);
            track.style.transform = 'translateX(' + translateX + '%)';
        }

        function next() {
            goTo(currentIndex + items);
        }

        function prev() {
            goTo(currentIndex - items);
        }

        var btnPrev = container.querySelector('.dsi-card-slider-prev');
        var btnNext = container.querySelector('.dsi-card-slider-next');

        if (btnPrev) {
            btnPrev.addEventListener('click', function() {
                prev();
                restartAutoplay();
            });
        }

        if (btnNext) {
            btnNext.addEventListener('click', function() {
                next();
                restartAutoplay();
            });
        }

        function startAutoplay() {
            if (interval <= 0) return;
            autoplayTimer = setInterval(next, interval);
        }

        function stopAutoplay() {
            if (autoplayTimer) clearInterval(autoplayTimer);
        }

        function restartAutoplay() {
            stopAutoplay();
            startAutoplay();
        }

        container.addEventListener('mouseenter', stopAutoplay);
        container.addEventListener('mouseleave', startAutoplay);

        goTo(0);
        startAutoplay();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var sliders = document.querySelectorAll('[data-dsi-slider="true"]');
        sliders.forEach(initSlider);
    });
})();
