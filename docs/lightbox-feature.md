# Lightbox Feature Documentation

## Overview

The lightbox feature allows users to enable a zoomable, interactive lightbox overlay when clicking on images in the CerneCMS editor and on the frontend. This feature uses PhotoSwipe 5, a modern, touch-friendly lightbox library.

## Features

- **Toggle per image**: Each image can have lightbox enabled or disabled independently
- **Touch-friendly**: Supports pinch-to-zoom, pan, and swipe gestures on mobile devices
- **Keyboard navigation**: Use arrow keys to navigate between images, ESC to close
- **Visual feedback**: Lightbox-enabled images show a pointer cursor and hover effect
- **Gallery navigation**: Multiple images in the same content area can be navigated as a gallery
- **Responsive**: Works seamlessly on desktop and mobile devices

## Architecture

### Components Involved

1. **ImageBlock.svelte** (`src/lib/components/blocks/ImageBlock.svelte`)
   - Frontend editor component for images
   - Displays a checkbox to toggle lightbox on/off
   - Shows visual indicator (cursor pointer) when lightbox is enabled

2. **editor.svelte.js** (`src/lib/stores/editor.svelte.js`)
   - Tiptap editor store
   - Extended the Image extension to support the `lightbox` attribute
   - Handles parsing and rendering of the lightbox attribute

3. **BlockRenderer.php** (`app/services/BlockRenderer.php`)
   - Backend renderer for converting Tiptap JSON to HTML
   - Renders images with PhotoSwipe data attributes when lightbox is enabled

4. **App.svelte** (`src/App.svelte`)
   - Main application component
   - Initializes PhotoSwipe on mount
   - Configures PhotoSwipe options

5. **index.css** (`src/index.css`)
   - Global styles
   - Adds cursor pointer and hover effects for lightbox-enabled images

### Data Flow

```
User toggles lightbox checkbox
    ↓
ImageBlock.svelte updates node attributes (lightbox: true/false)
    ↓
Tiptap stores attribute in JSON
    ↓
Content saved to database
    ↓
BlockRenderer.php renders HTML with data-pswp attributes
    ↓
Frontend displays image with cursor pointer
    ↓
PhotoSwipe initialized on page load
    ↓
User clicks image → Lightbox opens
```

## Implementation Details

### 1. PhotoSwipe Installation

PhotoSwipe 5 was installed via pnpm:

```bash
pnpm add photoswipe
```

### 2. ImageBlock Component Updates

The [`ImageBlock.svelte`](src/lib/components/blocks/ImageBlock.svelte) component was updated to:

- Add a `lightbox` derived property from node attributes
- Add a checkbox to toggle lightbox (only visible when image is selected)
- Add visual feedback (cursor pointer and hover effect) when lightbox is enabled
- Update node attributes when checkbox is toggled

```svelte
let lightbox = $derived(node.attrs.lightbox || false);

function handleLightboxToggle(e) {
    updateAttributes({ lightbox: e.target.checked });
}
```

### 3. Tiptap Image Extension Updates

The Image extension in [`editor.svelte.js`](src/lib/stores/editor.svelte.js:80-100) was extended to support the `lightbox` attribute:

```javascript
Image.configure({
    HTMLAttributes: {
        class: 'rounded-lg max-w-full',
    },
    addAttributes() {
        return {
            ...this.parent?.(),
            lightbox: {
                default: false,
                parseHTML: element => element.getAttribute('data-lightbox') === 'true',
                renderHTML: attributes => {
                    if (!attributes.lightbox) return {};
                    return { 'data-lightbox': 'true' };
                }
            }
        };
    }
})
```

### 4. BlockRenderer.php Updates

The [`BlockRenderer.php`](app/services/BlockRenderer.php:71-80) was updated to render images with PhotoSwipe attributes:

```php
case 'image':
    $src = $node['attrs']['src'] ?? '';
    $alt = $node['attrs']['alt'] ?? '';
    $title = $node['attrs']['title'] ?? '';
    $lightbox = $node['attrs']['lightbox'] ?? false;
    $caption = $title ? "<figcaption>{$title}</figcaption>" : "";

    $lightboxAttr = $lightbox ? 'data-pswp-width="auto" data-pswp-height="auto"' : '';
    $lightboxClass = $lightbox ? 'cursor-pointer hover:opacity-90' : '';

    return "<figure><img src=\"{$src}\" alt=\"{$alt}\" title=\"{$title}\" {$lightboxAttr} class=\"{$lightboxClass}\">{$caption}</figure>";
```

### 5. PhotoSwipe Initialization

PhotoSwipe is initialized in [`App.svelte`](src/App.svelte:1-30) using Svelte's `onMount` lifecycle function:

```javascript
import { onMount } from 'svelte';
import PhotoSwipeLightbox from 'photoswipe/lightbox';
import 'photoswipe/style.css';

onMount(() => {
    try {
        lightbox = new PhotoSwipeLightbox({
            gallery: '#editor-content, .page-content, .ProseMirror',
            children: 'img[data-pswp-width]',
            pswpModule: () => import('photoswipe'),
            padding: { top: 50, bottom: 50, left: 50, right: 50 },
            zoom: true,
            pinchToClose: true,
            tapAction: 'close',
            doubleTapAction: 'zoom',
        });
        lightbox.init();
    } catch (error) {
        console.error('Failed to initialize PhotoSwipe:', error);
    }

    return () => {
        if (lightbox) {
            try {
                lightbox.destroy();
            } catch (error) {
                console.error('Failed to destroy PhotoSwipe:', error);
            }
            lightbox = null;
        }
    };
});
```

**Note**: We use `onMount` instead of `$effect` to avoid infinite loop issues. The `onMount` lifecycle function runs only once when the component is mounted, making it ideal for initializing external libraries like PhotoSwipe.

### 6. CSS Styles

Lightbox styles were added to [`index.css`](src/index.css:96-120):

```css
img[data-pswp-width] {
    cursor: pointer;
    transition: opacity 0.2s ease;
}

img[data-pswp-width]:hover {
    opacity: 0.9;
}
```

## Usage

### In the Editor

1. Insert an image block in the editor
2. Click on the image to select it
3. Check the "Enable lightbox (click to zoom)" checkbox below the image
4. The image will now show a pointer cursor when hovered
5. Click on the image to open the lightbox

### On the Frontend

When viewing a page with lightbox-enabled images:
- Click on any image with lightbox enabled to open the lightbox
- Use arrow keys to navigate between images
- Use ESC key to close the lightbox
- Pinch to zoom on mobile devices
- Double-tap to zoom on mobile devices

## PhotoSwipe Configuration

The current PhotoSwipe configuration includes:

- **Gallery selector**: `#editor-content, .page-content, .ProseMirror` - Targets images in editor and page content
- **Child selector**: `img[data-pswp-width]` - Only targets images with the lightbox attribute
- **Padding**: 50px on all sides
- **Zoom**: Enabled
- **Pinch to close**: Enabled
- **Tap action**: Close lightbox
- **Double tap action**: Zoom

### Customizing PhotoSwipe

To customize PhotoSwipe behavior, modify the configuration in [`App.svelte`](src/App.svelte:14-23):

```javascript
lightbox = new PhotoSwipeLightbox({
    gallery: '#editor-content, .page-content, .ProseMirror',
    children: 'img[data-pswp-width]',
    pswpModule: () => import('photoswipe'),
    padding: { top: 50, bottom: 50, left: 50, right: 50 },
    zoom: true,
    pinchToClose: true,
    tapAction: 'close',
    doubleTapAction: 'zoom',
    // Add more options here
});
```

See [PhotoSwipe documentation](https://photoswipe.com/options/) for all available options.

## Testing

### Editor Testing

- [x] Toggle lightbox option appears in ImageBlock when image is selected
- [x] Toggle state persists when selecting/deselecting image
- [x] Lightbox attribute is saved in JSON
- [x] Lightbox indicator (cursor pointer) is visible when enabled

### Frontend Testing

To test the lightbox functionality:

1. Start the backend server: `php -S 127.0.0.1:8080 -t public`
2. Start the frontend dev server: `pnpm run dev`
3. Navigate to `http://localhost:8080/admin`
4. Create or edit a page
5. Add an image and enable lightbox
6. Save the page
7. View the page on the frontend
8. Click on the image to open the lightbox

### Test Checklist

- [ ] Images with lightbox enabled have cursor pointer
- [ ] Clicking lightbox-enabled image opens PhotoSwipe
- [ ] PhotoSwipe displays image correctly
- [ ] Zoom/pan gestures work
- [ ] Keyboard navigation works (ESC to close, arrows to navigate)
- [ ] Images without lightbox enabled don't trigger lightbox
- [ ] Multiple images in gallery work correctly
- [ ] Mobile responsive behavior works

## Troubleshooting

### Lightbox not opening

1. Check that the image has the `data-pswp-width` attribute in the rendered HTML
2. Verify that PhotoSwipe is initialized (check browser console for errors)
3. Ensure the image is within one of the gallery selectors (`#editor-content`, `.page-content`, `.ProseMirror`)

### Images not showing cursor pointer

1. Verify that the lightbox checkbox is checked in the editor
2. Check that the image has the `data-pswp-width` attribute
3. Ensure the CSS styles are loaded

### PhotoSwipe not initializing

1. Check browser console for JavaScript errors
2. Verify that `photoswipe` is installed: `pnpm list photoswipe`
3. Ensure the PhotoSwipe CSS is imported: `import 'photoswipe/style.css'`

## Files Modified

1. `package.json` - Added `photoswipe` dependency
2. `src/lib/components/blocks/ImageBlock.svelte` - Added lightbox toggle UI
3. `src/lib/stores/editor.svelte.js` - Extended Image extension with lightbox attribute
4. `app/services/BlockRenderer.php` - Render lightbox attributes in HTML
5. `src/App.svelte` - Initialize PhotoSwipe
6. `src/index.css` - Add lightbox styles

## Future Enhancements

Potential improvements to consider:

1. **Image dimensions**: Store image width/height in node attributes for better PhotoSwipe performance
2. **Lazy loading**: Load PhotoSwipe only when needed
3. **Custom captions**: Display image captions in the lightbox
4. **Thumbnails**: Show thumbnail navigation in the lightbox
5. **Download button**: Add a download button in the lightbox
6. **Share button**: Add social sharing options
7. **Fullscreen mode**: Add a fullscreen toggle button

## References

- [PhotoSwipe Documentation](https://photoswipe.com/)
- [PhotoSwipe Options](https://photoswipe.com/options/)
- [PhotoSwipe GitHub](https://github.com/dimsemenov/PhotoSwipe)
