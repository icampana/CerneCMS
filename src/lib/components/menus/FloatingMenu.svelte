<script>
    import { onMount } from 'svelte';
    import { editorStore } from '../../stores/editor.svelte.js';

    let element;

    const addImage = () => {
        editorStore.openMediaLibrary((url) => {
             editorStore.editor.chain().focus().setImage({ src: url }).run();
        });
    }
</script>

<div bind:this={element} class="floating-menu flex gap-1 items-center">
    {#if editorStore.editor}
        <button
            onclick={() => editorStore.editor.chain().focus().toggleHeading({ level: 1 }).run()}
            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-700 border border-gray-300 shadow-sm"
        >
            H1
        </button>
        <button
            onclick={() => editorStore.editor.chain().focus().toggleHeading({ level: 2 }).run()}
            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-700 border border-gray-300 shadow-sm"
        >
            H2
        </button>
        <button
            onclick={() => editorStore.editor.chain().focus().toggleBulletList().run()}
            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-700 border border-gray-300 shadow-sm"
        >
            List
        </button>
        <button
            onclick={addImage}
            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-700 border border-gray-300 shadow-sm"
        >
            Img
        </button>
        <button
            onclick={() => editorStore.editor.chain().focus().setGrid().run()}
            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-700 border border-gray-300 shadow-sm"
        >
            2 Col
        </button>
        <button
            onclick={() => editorStore.editor.chain().focus().setGrid3().run()}
            class="px-2 py-1 bg-gray-100 hover:bg-gray-200 rounded text-sm text-gray-700 border border-gray-300 shadow-sm"
        >
            3 Col
        </button>
    {/if}
</div>

<style>
    /* Let Tiptap/Tippy handle visibility */
</style>
