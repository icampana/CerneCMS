# Agent Guide: Implementing Widget Settings with the Drawer Pattern

## Overview

To maintain a clean and focused editing experience in CerneCMS, we avoid cluttering the editor surface with inline forms. Instead, we use a centralized **Block Settings Drawer** for configuring block attributes (e.g., captions, toggles, alignment).

## The Pattern

1.  **Trigger**: The block component (e.g., `ImageBlock`) displays a "Settings" (gear) button only when selected or hovered.
2.  **Action**: Clicking the button opens the global `BlockSettingsDrawer` via the `editorStore`.
3.  **State**: The store holds the current block's type, attributes, and an update callback.
4.  **UI**: The drawer renders the appropriate form fields based on the block type.

## Implementation Steps

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
