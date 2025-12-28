import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';

export function initLightbox() {
    let lightbox = null;

    try {
        lightbox = new PhotoSwipeLightbox({
            gallery: '#editor-content, .page-content, .ProseMirror, main',
            children: 'img[data-pswp-width]',
            pswpModule: () => import('photoswipe'),
            // Auto-detect image dimensions
            padding: { top: 50, bottom: 50, left: 50, right: 50 },
            // Zoom options
            zoom: true,
            pinchToClose: true,
            tapAction: 'close',
            doubleTapAction: 'zoom',
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
