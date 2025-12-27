<script>
    import { NodeViewWrapper } from 'svelte-tiptap';

    // Props from svelte-tiptap
    let { node, updateAttributes, selected, editor } = $props();

    let src = $derived(node.attrs.src);
    let alt = $derived(node.attrs.alt);
    let title = $derived(node.attrs.title || '');

    function handleCaptionChange(e) {
        updateAttributes({ title: e.target.value });
    }
</script>

<NodeViewWrapper class="image-block-wrapper my-8 {selected ? 'ring-2 ring-blue-500 rounded' : ''}">
    <figure class="flex flex-col items-center">
        <img {src} {alt} {title} class="max-w-full rounded-lg shadow-sm" />
        <input
            type="text"
            class="mt-2 text-center text-sm text-gray-500 border-none bg-transparent placeholder-gray-300 focus:ring-0 w-full"
            placeholder="Write a caption..."
            value={title}
            oninput={handleCaptionChange}
        />
    </figure>
</NodeViewWrapper>

<style>
    :global(.image-block-wrapper) {
        display: block;
    }
</style>
