<script>
    import { NodeViewWrapper } from 'svelte-tiptap';
    import { editorStore } from '../../stores/editor.svelte.js';
    import CogSolid from 'flowbite-svelte-icons/CogSolid.svelte';

    // Props from svelte-tiptap
    let { node, updateAttributes, selected, editor } = $props();

    let src = $derived(node.attrs.src);
    let alt = $derived(node.attrs.alt);
    let title = $derived(node.attrs.title || '');
    let lightbox = $derived(node.attrs.lightbox || false);

    function openSettings(e) {
        e.stopPropagation();
        editorStore.openBlockSettings('image', node.attrs, updateAttributes);
    }
</script>

<NodeViewWrapper class="image-block-wrapper my-8 relative group {selected ? 'ring-2 ring-blue-500 rounded' : ''}">
    <figure class="flex flex-col items-center relative">
        <img {src} {alt} {title} class="max-w-full rounded-lg shadow-sm {lightbox ? 'cursor-pointer hover:opacity-90' : ''}" />
        {#if title}
            <figcaption class="mt-2 text-center text-sm text-gray-500">{title}</figcaption>
        {/if}

        <!-- Settings Button (Visible on hover or selection) -->
        <button
            class="absolute top-2 right-2 bg-white/90 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white z-10"
            onclick={openSettings}
            title="Image Settings"
        >
            <CogSolid class="w-5 h-5 text-gray-700" />
        </button>
    </figure>
</NodeViewWrapper>

<style>
    :global(.image-block-wrapper) {
        display: block;
    }
</style>
