<script>
    import { BubbleMenu } from '@tiptap/extension-bubble-menu';
    import { onMount } from 'svelte';
    import { editorStore } from '../../stores/editor.svelte.js';

    let element;
    let plugin = $state(null);


</script>

<div bind:this={element} class="bubble-menu flex bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden p-1 gap-1">
    {#if editorStore.editor}
        <button
            onclick={() => editorStore.editor.chain().focus().toggleBold().run()}
            class="p-2 hover:bg-gray-100 rounded {editorStore.editor.isActive('bold') ? 'bg-gray-100 text-blue-600' : 'text-gray-700'}"
            aria-label="Bold"
        >
            <strong>B</strong>
        </button>
        <button
            onclick={() => editorStore.editor.chain().focus().toggleItalic().run()}
            class="p-2 hover:bg-gray-100 rounded {editorStore.editor.isActive('italic') ? 'bg-gray-100 text-blue-600' : 'text-gray-700'}"
            aria-label="Italic"
        >
            <em>i</em>
        </button>
        <button
            onclick={() => editorStore.editor.chain().focus().toggleStrike().run()}
            class="p-2 hover:bg-gray-100 rounded {editorStore.editor.isActive('strike') ? 'bg-gray-100 text-blue-600' : 'text-gray-700'}"
            aria-label="Strike"
        >
            <span class="line-through">S</span>
        </button>
    {/if}
</div>

<style>
    /* Let Tiptap/Tippy handle visibility */
</style>
