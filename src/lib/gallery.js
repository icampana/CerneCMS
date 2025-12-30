import Swiper from 'swiper';
import { Navigation, Pagination, Autoplay } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

export function initGalleries() {
    console.log('[Gallery] Initializing...', document.readyState);
    const selector = '.swiper-gallery';
    const elements = document.querySelectorAll(selector);
    console.log(`[Gallery] Found ${elements.length} galleries`);

    elements.forEach((el, index) => {
        // Find the actual swiper container inside the wrapper
        const container = el.querySelector('.swiper');
        if (!container) {
            console.warn(`[Gallery] No .swiper container found in gallery ${index}`);
            return;
        }

        // Skip if already initialized
        if (container.swiper) {
            console.log(`[Gallery] Gallery ${index} already initialized`);
            return;
        }

        // Read config
        const autoplayEnabled = el.dataset.autoplay === 'true';
        const autoplayDelay = parseInt(el.dataset.autoplaySpeed || '3000', 10);
        const gap = parseInt(el.dataset.gap || '8', 10);

        // Check for loop (more than 1 slide)
        const slides = container.querySelectorAll('.swiper-slide');
        const shouldLoop = slides.length > 1;

        console.log(`[Gallery] Initializing gallery ${index} with config:`, {
            autoplay: autoplayEnabled,
            delay: autoplayDelay,
            gap,
            loop: shouldLoop,
            slides: slides.length
        });

        try {
            const swiper = new Swiper(container, {
                modules: [Navigation, Pagination, Autoplay],
                loop: shouldLoop,
                autoplay: autoplayEnabled ? {
                    delay: autoplayDelay,
                    pauseOnMouseEnter: true,
                    disableOnInteraction: false,
                } : false,
                spaceBetween: gap,
                navigation: {
                    nextEl: el.querySelector('.swiper-button-next'),
                    prevEl: el.querySelector('.swiper-button-prev'),
                },
                pagination: {
                    el: el.querySelector('.swiper-pagination'),
                    clickable: true,
                },
            });
            console.log(`[Gallery] Gallery ${index} initialized successfully`, swiper);
        } catch (error) {
            console.error(`[Gallery] Failed to initialize gallery ${index}:`, error);
        }
    });
}
