<script>
    import { editorStore } from '../../stores/editor.svelte.js';

    let element;
</script>

<div bind:this={element} class="floating-menu flex gap-1 items-center bg-white p-1 rounded-lg border border-gray-200 shadow-lg">
    {#if editorStore.editor}
        <button
            onclick={() => editorStore.editor.chain().focus().toggleHeading({ level: 1 }).run()}
            class="px-2 py-1 hover:bg-gray-100 rounded text-sm text-gray-700 font-bold"
            title="Heading 1"
        >
            H1
        </button>
        <button
            onclick={() => editorStore.editor.chain().focus().toggleHeading({ level: 2 }).run()}
            class="px-2 py-1 hover:bg-gray-100 rounded text-sm text-gray-700 font-bold"
            title="Heading 2"
        >
            H2
        </button>
        <button
            onclick={() => editorStore.editor.chain().focus().toggleBulletList().run()}
            class="px-2 py-1 hover:bg-gray-100 rounded text-sm text-gray-700"
            title="Bullet List"
        >
            List
        </button>
    {/if}
</div>

<style>
    /* Hide floating menu by default - Tippy will show/position it when appropriate */
    .floating-menu {
        visibility: hidden;
        opacity: 0;
    }

    /* Tippy adds this attribute when showing the menu at the correct position */
    :global([data-tippy-root]) .floating-menu,
    :global(.tippy-box) .floating-menu {
        visibility: visible;
        opacity: 1;
    }

    /* When the menu is visible (controlled by Tippy), ensure it shows */
    :global(.floating-menu-container[data-show="true"]) .floating-menu {
        visibility: visible;
        opacity: 1;
    }
</style>

