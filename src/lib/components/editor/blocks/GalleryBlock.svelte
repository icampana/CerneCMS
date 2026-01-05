<script>
    import { NodeViewWrapper } from 'svelte-tiptap';
    import { editorStore } from '../../../stores/editor.svelte.js';
    import CogSolid from 'flowbite-svelte-icons/CogSolid.svelte';
    import PlusOutline from 'flowbite-svelte-icons/PlusOutline.svelte';
    import TrashBinSolid from 'flowbite-svelte-icons/TrashBinSolid.svelte';
    import { onMount, onDestroy } from 'svelte';
    import Swiper from 'swiper';
    import { Navigation, Pagination, Autoplay } from 'swiper/modules';
    import 'swiper/css';
    import 'swiper/css/navigation';
    import 'swiper/css/pagination';

    let { node, updateAttributes, selected, editor } = $props();

    let layout = $derived(node.attrs.layout || 'standard');
    let showCaptions = $derived(node.attrs.showCaptions || false);
    let autoplay = $derived(node.attrs.autoplay !== false);
    let autoplaySpeed = $derived(node.attrs.autoplaySpeed || 3000);
    let gap = $derived(node.attrs.gap || 8);
    let columns = $derived(node.attrs.columns || 3);

    // Parse images from node content
    let images = $derived(node.attrs.images || []);

    let swiperInstance = null; // Non-reactive explicitly
    let swiperContainer = $state(null);

    // Initialize Swiper for slideshow layout
    $effect(() => {
        if (layout === 'slideshow' && swiperContainer && images.length > 0) {
            // Cleanup provided by return function below handles previous instances
            // We use untrack if we needed to access reactive state without dependency,
            // but swiperInstance is now plain.

            swiperInstance = new Swiper(swiperContainer, {
                modules: [Navigation, Pagination, Autoplay],
                loop: images.length > 1,
                autoplay: autoplay ? {
                    delay: autoplaySpeed,
                    pauseOnMouseEnter: true,
                    disableOnInteraction: false,
                } : false,
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                spaceBetween: gap,
            });
        }

        return () => {
            if (swiperInstance) {
                swiperInstance.destroy();
                swiperInstance = null;
            }
        };
    });

    function openSettings(e) {
        e.stopPropagation();
        editorStore.openBlockSettings('gallery', node.attrs, updateAttributes, node);
    }

    function addImage() {
        editorStore.openMediaLibrary((url) => {
            const newImage = {
                src: url,
                alt: '',
                caption: '',
                width: 800,
                height: 600,
                id: crypto.randomUUID() // Add ID for better keying if needed
            };

            const newImages = [...(node.attrs.images || []), newImage];
            updateAttributes({ images: newImages });
        });
    }

    function removeImage(index) {
        const currentImages = node.attrs.images || [];
        const newImages = currentImages.filter((_, i) => i !== index);
        updateAttributes({ images: newImages });
    }
</script>

<NodeViewWrapper class="gallery-wrapper relative my-8 group {selected ? 'ring-2 ring-blue-500 rounded-lg' : ''}">
    <!-- Settings Button -->
    <button
        class="absolute top-2 right-2 bg-white/90 p-2 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity hover:bg-white z-20"
        onclick={openSettings}
        title="Gallery Settings"
    >
        <CogSolid class="w-5 h-5 text-gray-700" />
    </button>

    <!-- Empty State -->
    {#if images.length === 0}
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center">
            <PlusOutline class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p class="text-gray-500 mb-4">No images in gallery</p>
            <button
                onclick={addImage}
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
                Add Images
            </button>
        </div>
    {:else}
        <!-- Masonry Layout -->
        {#if layout === 'masonry'}
            <div
                class="masonry-gallery"
                style:column-count={columns}
                style:column-gap={`${gap}px`}
            >
                {#each images as image, index}
                    <div class="masonry-item mb-4 relative group/item" style:break-inside="avoid">
                        <img
                            src={image.src}
                            alt={image.alt}
                            class="w-full rounded-lg"
                        />
                        {#if showCaptions && image.caption}
                            <p class="mt-2 text-sm text-gray-600">{image.caption}</p>
                        {/if}
                        <button
                            class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded opacity-0 group-hover/item:opacity-100 transition-opacity"
                            onclick={(e) => { e.stopPropagation(); removeImage(index); }}
                            title="Remove image"
                        >
                            <TrashBinSolid class="w-4 h-4" />
                        </button>
                    </div>
                {/each}
            </div>
        {:else if layout === 'slideshow'}
            <!-- Slideshow Layout -->
            <div class="swiper-gallery relative">
                <div class="swiper" bind:this={swiperContainer}>
                    <div class="swiper-wrapper">
                        {#each images as image, index}
                            <div class="swiper-slide relative">
                                <img
                                    src={image.src}
                                    alt={image.alt}
                                    class="w-full h-auto rounded-lg"
                                />
                                {#if showCaptions && image.caption}
                                    <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white p-4 rounded-b-lg">
                                        <p class="text-sm">{image.caption}</p>
                                    </div>
                                {/if}
                            </div>
                        {/each}
                    </div>
                    <!-- Navigation -->
                    {#if images.length > 1}
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-pagination"></div>
                    {/if}
                </div>
            </div>
        {:else}
            <!-- Standard Layout -->
            <div
                class="standard-gallery grid"
                style:grid-template-columns={`repeat(${columns}, 1fr)`}
                style:gap={`${gap}px`}
            >
                {#each images as image, index}
                    <div class="standard-item relative group/item">
                        <img
                            src={image.src}
                            alt={image.alt}
                            class="w-full h-48 object-cover rounded-lg"
                        />
                        {#if showCaptions && image.caption}
                            <p class="mt-2 text-sm text-gray-600">{image.caption}</p>
                        {/if}
                        <button
                            class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded opacity-0 group-hover/item:opacity-100 transition-opacity"
                            onclick={(e) => { e.stopPropagation(); removeImage(index); }}
                            title="Remove image"
                        >
                            <TrashBinSolid class="w-4 h-4" />
                        </button>
                    </div>
                {/each}
            </div>
        {/if}

        <!-- Add Image Button -->
        <button
            onclick={addImage}
            class="mt-4 flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors text-gray-700"
        >
            <PlusOutline class="w-5 h-5" />
            <span>Add Image</span>
        </button>
    {/if}
</NodeViewWrapper>

<style>
    :global(.gallery-wrapper) {
        margin: 2rem 0;
    }

    .masonry-gallery {
        column-count: 3;
    }

    @media (max-width: 768px) {
        .masonry-gallery {
            column-count: 2;
        }
    }

    @media (max-width: 480px) {
        .masonry-gallery {
            column-count: 1;
        }
    }

    .standard-gallery {
        grid-template-columns: repeat(3, 1fr);
    }

    @media (max-width: 768px) {
        .standard-gallery {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 480px) {
        .standard-gallery {
            grid-template-columns: 1fr;
        }
    }

    :global(.swiper-button-next),
    :global(.swiper-button-prev) {
        color: white;
        background: rgba(0, 0, 0, 0.5);
        padding: 20px;
        border-radius: 50%;
    }

    :global(.swiper-pagination-bullet) {
        background: white;
        opacity: 0.5;
    }

    :global(.swiper-pagination-bullet-active) {
        opacity: 1;
    }
</style>
