<script>
    import { onMount, onDestroy } from 'svelte';
    import { editorStore } from '../stores/editor.svelte.js';
    import BubbleMenu from './menus/BubbleMenu.svelte';
    import FloatingMenu from './menus/FloatingMenu.svelte';

    let element;
    let bubbleMenuComponent;
    let floatingMenuComponent;

    // In Svelte 5 with Tiptap menus, we need the DOM elements of the menus *before* we init Tiptap.
    // The Menu components need to render their divs and expose them.

    let bubbleMenuEl = $state(null);
    let floatingMenuEl = $state(null);

    onMount(() => {
        // Wait for bind:this to populate
        // Small timeout to ensure DOM is ready? Usually onMount is fine.
        setTimeout(() => {
             editorStore.init(element, bubbleMenuEl, floatingMenuEl);
        }, 0);
    });

    onDestroy(() => {
        editorStore.destroy();
    });
</script>

<div class="editor-wrapper border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm relative group">
    <!-- Menus -->
    <!-- We pass bind:this to get the component instance, but we actually need the DOM element inside. -->
    <!-- Simpler: define the divs here and pass them to the store, and just use the logic in the components? -->
    <!-- Or better: Pass the DOM element BINDING down? -->

    <!-- Refactored approach: We define the menu containers HERE so we have the refs,
         and pass them to the store. The components just hold the buttons. -->

    <div bind:this={bubbleMenuEl} class="bubble-menu-container">
        <BubbleMenu />
    </div>

    <div bind:this={floatingMenuEl} class="floating-menu-container">
        <FloatingMenu />
    </div>

    <!-- Header / Toolbar -->
    <div class="bg-white border-b border-gray-200 p-4 flex items-center justify-between gap-4 sticky top-0 z-10">
        <!-- Title Input -->
        <input
            type="text"
            bind:value={editorStore.title}
            placeholder="Page Title"
            class="text-xl font-bold text-gray-800 border-none focus:ring-0 placeholder-gray-300 bg-transparent flex-1"
        />

        <!-- Actions -->
        <div class="flex items-center gap-2">
            <span class="text-xs text-gray-400">
                {editorStore.isSaving ? 'Saving...' : editorStore.lastSaved ? 'Saved ' + editorStore.lastSaved.toLocaleTimeString() : 'Unsaved'}
            </span>
            <button
                onclick={() => editorStore.save()}
                disabled={editorStore.isSaving}
                class="px-4 py-2 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 disabled:opacity-50 transition-colors"
            >
                Save
            </button>
        </div>
    </div>

    <!-- Tiptap Instance -->
    <div bind:this={element} class="prose prose-lg max-w-none p-8 min-h-[500px] outline-none mx-auto"></div>

    <!-- Debug Output (Dev Only) -->
    <div class="bg-gray-900 text-gray-100 p-4 text-xs font-mono border-t border-gray-800">
        <div class="uppercase tracking-widest text-gray-500 mb-2">Debug Content State</div>
        {editorStore.content}
    </div>
</div>

<style>
    /* Scoped styles if needed, mostly using Tailwind */
    :global(.ProseMirror) {
        outline: none;
    }
</style>
