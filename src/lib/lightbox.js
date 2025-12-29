import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';

export function initLightbox() {
    let lightbox = null;

    try {
        lightbox = new PhotoSwipeLightbox({
            gallery: '#editor-content, .page-content, .ProseMirror, main',
            children: 'a.lightbox-trigger', // Target the anchor tag now
            pswpModule: () => import('photoswipe'),
            // Auto-detect image dimensions
            padding: { top: 50, bottom: 50, left: 50, right: 50 },
            // Zoom options
            zoom: true,
            pinchToClose: true,
            tapAction: 'close',
            doubleTapAction: 'zoom',
        });

        // Use domItemData filter to set dimensions from DOM elements
        lightbox.addFilter('domItemData', (itemData, element, linkEl) => {
            // element is the anchor tag (.lightbox-trigger)
            if (!element || typeof element.querySelector !== 'function') {
                return itemData;
            }

            const img = element.querySelector('img');
            if (img) {
                // Use natural dimensions if loaded, otherwise fallback
                const w = img.naturalWidth || img.width || 800;
                const h = img.naturalHeight || img.height || 600;

                return {
                    ...itemData,
                    src: img.src,
                    w: w,
                    h: h,
                    msrc: img.src, // thumbnail
                    element: element
                };
            }
            return itemData;
        });

        lightbox.init();
        console.log('Lightbox initialized');
    } catch (error) {
        console.error('Failed to initialize PhotoSwipe:', error);
    }

    // Return destroy function
    return () => {
        if (lightbox) {
            try {
                lightbox.destroy();
                console.log('Lightbox destroyed');
            } catch (error) {
                console.error('Failed to destroy PhotoSwipe:', error);
            }
            lightbox = null;
        }
    };
}
