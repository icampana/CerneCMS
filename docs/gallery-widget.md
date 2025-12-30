# Gallery Widget Documentation

## Overview

The Gallery Widget is a powerful block component that allows users to create image galleries with multiple layout options. It supports three distinct display modes: Masonry, Slideshow, and Standard Grid layouts.

## Features

- **Multiple Layout Options**: Choose between Masonry, Slideshow, or Standard layouts
- **Image Management**: Add, remove, and caption images from the media library
- **Configurable Settings**: Adjust columns, gap size, and caption visibility
- **Slideshow Controls**: Auto-play with configurable speed, pause on hover, navigation arrows, and pagination dots
- **Responsive Design**: All layouts adapt to different screen sizes
- **Optional Captions**: Enable/disable captions for individual images

## Architecture

### Data Structure

The gallery block stores its data in the following structure:

```javascript
{
  type: 'gallery',
  attrs: {
    layout: 'masonry' | 'slideshow' | 'standard',
    showCaptions: true | false,
    autoplay: true | false,
    autoplaySpeed: 3000, // milliseconds
    gap: 8, // pixels between images
    columns: 3, // for standard and masonry layouts
  },
  content: [
    {
      type: 'galleryItem',
      attrs: {
        src: '/uploads/image.jpg',
        alt: 'Image description',
        caption: 'Optional caption text',
        width: 800,
        height: 600
      }
    }
  ]
}
```

### Component Structure

```
src/lib/
├── editor/
│   └── extensions/
│       └── Gallery.js          # Tiptap extension definition
├── components/
│   ├── editor/
│   │   └── blocks/
│   │       └── GalleryBlock.svelte  # Main gallery component
│   └── drawers/
│       └── BlockSettingsDrawer.svelte  # Gallery settings UI
└── stores/
    └── editor.svelte.js          # Editor state management

app/
└── services/
    └── BlockRenderer.php            # Backend rendering
```

## Layout Options

### 1. Masonry Layout

A Pinterest-style layout with varying image heights based on aspect ratios.

**Technical Implementation:**
- Uses CSS columns: `column-count: 3; column-gap: 8px;`
- Images use `break-inside: avoid;` to prevent splitting
- Responsive breakpoints: 3 columns (desktop), 2 columns (tablet), 1 column (mobile)

**Best For:**
- Images with varying aspect ratios
- Pinterest-style visual presentation
- Showcasing photography portfolios

### 2. Slideshow Layout

A carousel with navigation and auto-play capabilities using Swiper.js.

**Technical Implementation:**
- Powered by Swiper.js library
- Features:
  - Navigation arrows (prev/next)
  - Pagination dots
  - Auto-play with configurable speed (1000-10000ms)
  - Pause on hover
  - Touch/swipe support for mobile
  - Loop mode for continuous playback

**Best For:**
- Featured image showcases
- Product galleries
- Hero sections
- Mobile-first experiences

### 3. Standard Layout

A regular grid with uniform image sizes.

**Technical Implementation:**
- Uses CSS Grid: `grid-template-columns: repeat(3, 1fr);`
- Fixed height images: `h-48 object-cover`
- Responsive breakpoints: 3 columns (desktop), 2 columns (tablet), 1 column (mobile)

**Best For:**
- Uniform image collections
- Thumbnail galleries
- Consistent visual presentation

## Settings Reference

### Layout Selector

Choose between three layout modes:
- **Masonry**: Pinterest-style with varying heights
- **Slideshow**: Carousel with auto-play and navigation
- **Standard**: Regular grid with uniform sizes

### Columns

Controls the number of columns for Masonry and Standard layouts:
- Range: 1-5 columns
- Default: 3 columns
- Affects: Masonry and Standard layouts only

### Gap Size

Controls spacing between images:
- Range: 0-32 pixels
- Step: 4 pixels
- Default: 8 pixels
- Affects: All layouts

### Show Captions

Toggle caption display:
- Default: Disabled
- When enabled: Shows caption text below each image
- Captions are editable per image

### Auto-play (Slideshow Only)

Controls automatic slide advancement:
- Default: Enabled
- Affects: Slideshow layout only
- Pauses on hover

### Auto-play Speed (Slideshow Only)

Controls slide transition timing:
- Range: 1000-10000 milliseconds (1-10 seconds)
- Step: 500 milliseconds
- Default: 3000 milliseconds (3 seconds)
- Affects: Slideshow layout only

## Usage Instructions

### Adding a Gallery

1. Click the **Gallery** icon in the ComponentToolbar (camera photo icon)
2. Or drag the Gallery component from the toolbar to the editor

### Adding Images

1. Click the **+ Add Image** button in the gallery
2. Select images from the Media Library
3. Images are added to the gallery

### Removing Images

1. Hover over an image in the gallery
2. Click the red trash icon that appears
3. Image is removed from the gallery

### Editing Captions

1. Open the gallery settings by clicking the gear icon
2. Enable **Show Captions** toggle
3. Edit caption text for each image in the settings drawer

### Changing Layout

1. Open gallery settings
2. Click the desired layout button (Masonry, Slideshow, or Standard)
3. Gallery updates to the new layout

## Implementation Details

### Frontend (Editor)

**Gallery Extension** (`src/lib/editor/extensions/Gallery.js`)
- Defines the Tiptap node type
- Specifies attributes for configuration
- Uses SvelteNodeViewRenderer for the GalleryBlock component

**GalleryBlock Component** (`src/lib/components/editor/blocks/GalleryBlock.svelte`)
- Renders the gallery in the editor
- Handles image management (add/remove)
- Initializes Swiper.js for slideshow layout
- Provides settings button for configuration

**Settings Drawer** (`src/lib/components/drawers/BlockSettingsDrawer.svelte`)
- Integrated gallery settings UI
- Layout selector with visual previews
- Image list with caption editing
- Configuration controls (columns, gap, captions, auto-play)

### Backend (Frontend Rendering)

**BlockRenderer** (`app/services/BlockRenderer.php`)
- Renders gallery HTML for the public frontend
- Handles all three layouts
- Includes Swiper.js initialization script for slideshow
- Generates unique IDs for multiple galleries on a page

## Code Examples

### Creating a Gallery Programmatically

```javascript
// Insert a gallery with default settings
editor.chain().focus().insertContent({ type: 'gallery' }).run();

// Insert a gallery with specific settings
editor.chain().focus().insertContent({
  type: 'gallery',
  attrs: {
    layout: 'masonry',
    showCaptions: true,
    columns: 4,
    gap: 16
  }
}).run();
```

### Adding Images to Gallery

```javascript
// Get current gallery node
const galleryNode = editor.state.doc.nodeAt(pos);

// Create new image item
const newImage = {
  type: 'galleryItem',
  attrs: {
    src: '/uploads/my-image.jpg',
    alt: 'My Image',
    caption: 'A beautiful sunset',
    width: 800,
    height: 600
  }
};

// Update gallery content
const newContent = [...galleryNode.content, newImage];
const tr = editor.state.tr.setNodeMarkup(pos, null, galleryNode.attrs, newContent);
editor.view.dispatch(tr);
```

## Dependencies

### Required Packages

```json
{
  "dependencies": {
    "swiper": "^12.0.3"
  }
}
```

**Swiper.js** provides:
- Touch/swipe support
- Navigation arrows and pagination
- Auto-play with pause on hover
- Responsive breakpoints
- Accessibility features

### Existing Packages Used

- **flowbite-svelte-icons**: UI icons (CameraPhotoSolid, PlusOutline, TrashBinSolid)
- **svelte-tiptap**: Tiptap integration with Svelte
- **flowbite-svelte**: UI components (Drawer, Label, Input, Checkbox, Select, Range)

## Styling

### CSS Classes Used

- **Tailwind CSS**: All styling uses Tailwind utility classes
- **Custom CSS**: Defined in component `<style>` blocks
- **Responsive**: Mobile-first approach with breakpoints at 480px and 768px

### Swiper Customization

```css
:global(.swiper-button-next),
:global(.swiper-button-prev) {
    color: white;
    background: rgba(0, 0, 0, 0.5);
    padding: 20px;
    border-radius: 50%;
}

:global(.swiper-pagination-bullet) {
    background: white;
    opacity: 0.5;
}

:global(.swiper-pagination-bullet-active) {
    opacity: 1;
}
```

## Browser Compatibility

- **Modern Browsers**: Full support (Chrome, Firefox, Safari, Edge)
- **IE11**: Not supported (uses CSS Grid and CSS Columns)
- **Mobile**: Full touch/swipe support via Swiper.js

## Performance Considerations

- **Image Loading**: Images load lazily in slideshow
- **Swiper Initialization**: Only initializes when layout is slideshow
- **Cleanup**: Swiper instances are properly destroyed on component unmount
- **Bundle Size**: Swiper.js adds ~60KB (gzipped: ~17KB)

## Accessibility

- **Keyboard Navigation**: Slideshow supports arrow keys (via Swiper)
- **Screen Readers**: Alt text on all images
- **Focus Management**: Settings drawer is dismissible
- **ARIA Labels**: Navigation buttons have appropriate labels

## Future Enhancements

Potential improvements for future versions:
- Drag-and-drop image reordering
- Image lightbox integration
- Filter/categorization support
- Lazy loading options
- Custom aspect ratio modes
- Gallery templates/presets
