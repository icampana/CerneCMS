<script>
    import { NodeViewWrapper } from 'svelte-tiptap';
    import { editorStore } from '../../../stores/editor.svelte.js';
    import CogSolid from 'flowbite-svelte-icons/CogSolid.svelte';

    let { node, updateAttributes, selected } = $props();

    let layout = $derived(node.attrs.layout || 'centered');
    let backgroundImage = $derived(node.attrs.backgroundImage || '');
    let buttonUrl = $derived(node.attrs.buttonUrl || '#');
    let textColor = $derived(node.attrs.textColor || 'auto');

    // Determine actual text color to use
    let actualTextColor = $derived(() => {
        if (textColor === 'light') return 'text-white';
        if (textColor === 'dark') return 'text-gray-900';
        // Auto: white for hero/bg, dark otherwise
        return (layout === 'hero' || backgroundImage) ? 'text-white' : 'text-gray-900';
    });

    // Svelte action to set innerHTML once and never update it reactively
    function initContent(element, initialValue) {
        element.innerHTML = initialValue || '';
        return {
            update(newValue) {
                // Don't update - let contenteditable manage its own state
                // This prevents cursor jumping
            }
        };
    }

    function handleInput(field, event) {
        updateAttributes({ [field]: event.target.textContent });
    }

    function openSettings(e) {
        e.stopPropagation();
        editorStore.openBlockSettings('cta', node.attrs, updateAttributes);
    }
</script>

<NodeViewWrapper class="cta-wrapper relative my-8 group {selected ? 'ring-2 ring-blue-500 rounded-lg' : ''}">

    <!-- Settings Button (Visible on hover or selection) -->
    <button
        class="absolute top-2 right-2 bg-white/90 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white z-20"
        onclick={openSettings}
        title="CTA Settings"
    >
        <CogSolid class="w-5 h-5 text-gray-700" />
    </button>

    <!-- Layouts -->
    <div
        class="cta-container rounded-2xl overflow-hidden relative shadow-lg transition-all duration-300"
        class:bg-gray-50={!backgroundImage && layout !== 'hero'}
        class:bg-gradient-to-br={layout === 'hero' && !backgroundImage}
        class:from-blue-600={layout === 'hero' && !backgroundImage}
        class:to-blue-800={layout === 'hero' && !backgroundImage}
        style:background-image={backgroundImage ? `url(${backgroundImage})` : 'none'}
        style:background-size="cover"
        style:background-position="center"
    >
        <!-- Overlay -->
        {#if backgroundImage || layout === 'hero'}
            <div class="absolute inset-0 bg-black/40 pointer-events-none"></div>
        {/if}

        <div class="relative z-10 p-8 md:p-12 flex flex-col gap-6 {layout === 'centered' ? 'items-center text-center' : ''} {layout === 'split' ? 'md:flex-row md:items-center md:justify-between' : ''} {layout === 'hero' ? 'items-center text-center py-20 min-h-[400px]' : ''} {actualTextColor()}">

            <div class="flex flex-col gap-4 max-w-2xl">
                <!-- svelte-ignore a11y_missing_content -->
                <h2
                    contenteditable
                    use:initContent={node.attrs.title}
                    oninput={(e) => handleInput('title', e)}
                    class="{layout === 'hero' ? 'text-4xl md:text-5xl' : 'text-3xl md:text-4xl'} font-bold outline-none empty:before:content-['Title'] empty:before:text-gray-400"
                ></h2>

                <p
                    contenteditable
                    use:initContent={node.attrs.subtitle}
                    oninput={(e) => handleInput('subtitle', e)}
                    class="{layout === 'hero' ? 'text-xl' : 'text-lg'} opacity-90 outline-none empty:before:content-['Subtitle'] empty:before:text-gray-300"
                ></p>
            </div>

            <div class="shrink-0">
                <a
                    href={buttonUrl}
                    aria-label="CTA Action"
                    onclick={(e) => e.preventDefault()}
                    class="inline-block px-6 py-3 rounded-lg font-semibold transition-transform hover:scale-105 active:scale-95
                           {layout === 'hero' || backgroundImage ? 'bg-white text-gray-900 hover:bg-gray-100' : 'bg-blue-600 text-white hover:bg-blue-700'}"
                >
                    <span
                        contenteditable
                        use:initContent={node.attrs.buttonText}
                        oninput={(e) => handleInput('buttonText', e)}
                        class="outline-none"
                    ></span>
                </a>
            </div>

        </div>
    </div>

</NodeViewWrapper>

<style>
    :global(.cta-wrapper) {
        margin: 2rem 0;
    }
</style>
