# Agent Guide: Implementing Widget Settings with the Drawer Pattern

## Overview

To maintain a clean and focused editing experience in CerneCMS, we avoid cluttering the editor surface with inline forms. Instead, we use a centralized **Block Settings Drawer** for configuring block attributes (e.g., captions, toggles, alignment).

## Creating a New Block Type

This guide covers the end-to-end process of creating a new block (widget) in CerneCMS, from backend rendering to frontend editing and configuration.

### 1. Backend: Block Rendering (PHP)
**File:** `app/services/BlockRenderer.php`

Define how the block's JSON data matches to HTML for the public frontend. Add a new `case` to the `renderNode` switch statement.

```php
case 'my-block':
    $title = htmlspecialchars($node['attrs']['title'] ?? '');
    return "<div class=\"my-block\"><h3>{$title}</h3></div>";
```

### 2. Frontend: Tiptap Extension (JS)
**Location:** `src/lib/editor/extensions/` or inline in `src/lib/stores/editor.svelte.js`

Define the Tiptap node extension. This tells the editor how to treat the block (e.g., as a block, atomic, etc.) and how to render it in the editor.

**File:** `src/lib/stores/editor.svelte.js` (Import section)
```javascript
import MyBlockExtension from '../editor/extensions/MyBlock.js';
```

**File:** `src/lib/stores/editor.svelte.js` (Extensions array)
```javascript
extensions: [
    // ...
    MyBlockExtension,
    // ...
]
```

### 3. Frontend: Drag-and-Drop Handling
**File:** `src/lib/stores/editor.svelte.js`

Update the `handleDrop` function in the `editorProps` to handle the new block type when dropped from the toolbar.

```javascript
} else if (type === 'my-block') {
    view.dispatch(view.state.tr.insert(pos, view.state.schema.nodes.myBlock.create()));
}
```

### 4. Frontend: Toolbar Icon
**File:** `src/lib/components/ComponentToolbar.svelte`

Add a drag source (and optionally a click handler) for the new block.

```svelte
<div
    draggable="true"
    on:dragstart={(e) => handleDragStart(e, 'my-block')}
    class="..."
>
    <!-- Icon -->
</div>
```

---

## Adding Settings to a Block (The Drawer Pattern)

To maintain a clean and focused editing experience in CerneCMS, we avoid cluttering the editor surface with inline forms. Instead, we use a centralized **Block Settings Drawer** for configuring block attributes (e.g., captions, toggles, alignment).

### Implementation Steps

When adding settings to a new or existing widget, follow these steps:

### 1. Update the Store Logic (if needed)
The `editorStore` already handles generic types, but verify `blockSettings` state structure in `src/lib/stores/editor.svelte.js` if you need complex state. Usually, no changes are needed here.

### 2. Update `BlockSettingsDrawer.svelte`
File: `src/lib/components/drawers/BlockSettingsDrawer.svelte`

Add a condition for your block type (`{#if editorStore.blockSettings.type === 'your-block-type'}`) and add the input fields. Map them to the attributes using the `update('attributeName', value)` helper.

```svelte
{#if editorStore.blockSettings.type === 'video'}
    <div class="space-y-6">
        <div>
            <Label class="mb-2">Video URL</Label>
            <Input
                type="text"
                value={editorStore.blockSettings.attributes.src || ''}
                oninput={(e) => update('src', e.target.value)}
            />
        </div>
        <!-- Add more fields here -->
    </div>
{/if}
```

### 3. Update the Block Component
File: `src/lib/components/blocks/YourBlock.svelte`

1.  Import dependencies:
    ```javascript
    import { editorStore } from '../../stores/editor.svelte.js';
    import { CogSolid } from 'flowbite-svelte-icons';
    ```

2.  Add the open handler:
    ```javascript
    function openSettings(e) {
        e.stopPropagation();
        // 'your-block-type' must match the check in the Drawer
        // node.attrs contains the current attributes
        // updateAttributes is the Tiptap callback to save changes
        editorStore.openBlockSettings('your-block-type', node.attrs, updateAttributes);
    }
    ```

3.  Add the Settings Button in the template (positioned absolutely):
    ```svelte
    <NodeViewWrapper class="relative group ...">
        <div class="relative ...">
            <!-- Your Block Content -->

            <!-- Settings Button -->
            <button
                class="absolute top-2 right-2 bg-white/90 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white z-10"
                onclick={openSettings}
                title="Settings"
            >
                <CogSolid class="w-5 h-5 text-gray-700" />
            </button>
        </div>
    </NodeViewWrapper>
    ```

## Best Practices

*   **Clean UI**: Keep block components minimal. Only show the primary content.
*   **Immediate Feedback**: The `updateAttributes` callback in Tiptap is reactive. Changes in the drawer should immediately reflect in the editor (if visible).
*   **Accessibility**: Always add `title` or `aria-label` to the settings button.
