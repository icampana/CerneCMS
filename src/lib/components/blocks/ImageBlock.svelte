<script>
    import { NodeViewWrapper } from 'svelte-tiptap';

    // Props from svelte-tiptap
    let { node, updateAttributes, selected, editor } = $props();

    let src = $derived(node.attrs.src);
    let alt = $derived(node.attrs.alt);
    let title = $derived(node.attrs.title || '');
    let lightbox = $derived(node.attrs.lightbox || false);

    function handleCaptionChange(e) {
        updateAttributes({ title: e.target.value });
    }

    function handleLightboxToggle(e) {
        updateAttributes({ lightbox: e.target.checked });
    }
</script>

<NodeViewWrapper class="image-block-wrapper my-8 {selected ? 'ring-2 ring-blue-500 rounded' : ''}">
    <figure class="flex flex-col items-center">
        <img {src} {alt} {title} class="max-w-full rounded-lg shadow-sm {lightbox ? 'cursor-pointer hover:opacity-90' : ''}" />
        <input
            type="text"
            class="mt-2 text-center text-sm text-gray-500 border-none bg-transparent placeholder-gray-300 focus:ring-0 w-full"
            placeholder="Write a caption..."
            value={title}
            oninput={handleCaptionChange}
        />
        {#if selected}
            <div class="mt-2 flex items-center gap-2">
                <input
                    type="checkbox"
                    id="lightbox-toggle"
                    checked={lightbox}
                    onchange={handleLightboxToggle}
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                />
                <label for="lightbox-toggle" class="text-sm text-gray-600 cursor-pointer">
                    Enable lightbox (click to zoom)
                </label>
            </div>
        {/if}
    </figure>
</NodeViewWrapper>

<style>
    :global(.image-block-wrapper) {
        display: block;
    }
</style>
