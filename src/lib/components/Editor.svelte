<script>
    import { onMount, onDestroy, tick } from 'svelte';
    import { Breadcrumb, BreadcrumbItem } from 'flowbite-svelte';
    import CogOutline from 'flowbite-svelte-icons/CogOutline.svelte';
    import WindowSolid from 'flowbite-svelte-icons/WindowSolid.svelte';
    import WindowRestoreSolid from 'flowbite-svelte-icons/WindowRestoreSolid.svelte';
    import { editorStore } from '../stores/editor.svelte.js';
    import { push } from 'svelte-spa-router'; // Import push
    import BubbleMenu from './menus/BubbleMenu.svelte';
    import BlockOptionsMenu from './menus/BlockOptionsMenu.svelte';
    import ComponentToolbar from './ComponentToolbar.svelte';

    import PageSettingsDrawer from './PageSettingsDrawer.svelte';
    import EditorSkeleton from './ui/EditorSkeleton.svelte';

    // Get params from router
    let { params = {} } = $props();
    let pageId = $derived(params.pageId);

    let element = $state();
    let bubbleMenuEl = $state(null);
    let loading = $state(true);

    onMount(async () => {
        // Load Data if pageId is present
        if (pageId && pageId !== 'new') {
            try {
                const res = await fetch(`/api/pages/${pageId}`);
                if (res.ok) {
                    const data = await res.json();
                    editorStore.load(data);
                }
            } catch (e) {
                console.error("Failed to load page", e);
            }
        } else {
            // Reset for new page
            editorStore.reset();
        }

        // Hide loader and render editor DOM
        loading = false;
        await tick();

        // Init Editor now that DOM is ready
        if (element) {
            editorStore.init(element, bubbleMenuEl);
        }
    });

    onDestroy(() => {
        editorStore.destroy();
    });
</script>

<div class="editor-wrapper border border-gray-200 rounded-lg bg-white shadow-sm relative group transition-all duration-300 {editorStore.zenModeEnabled ? '!border-0 !rounded-none !shadow-none min-h-screen' : ''}">
    {#if loading}
        <EditorSkeleton />
    {:else}
        <!-- Menus -->
    <!-- We pass bind:this to get the component instance, but we actually need the DOM element inside. -->
    <!-- Simpler: define the divs here and pass them to the store, and just use the logic in the components? -->
    <!-- Or better: Pass the DOM element BINDING down? -->

    <!-- Refactored approach: We define the menu containers HERE so we have the refs,
         and pass them to the store. The components just hold the buttons. -->

    <div bind:this={bubbleMenuEl} class="bubble-menu-container absolute top-0 left-0 z-50 invisible">
        <BubbleMenu />
    </div>

    <BlockOptionsMenu />

    <!-- Header / Toolbar -->
    <div class="bg-white border-b border-gray-200 p-4 sticky top-0 z-20">
        <!-- Breadcrumb Navigation -->
        <div class="flex items-center justify-between mb-3">
            <Breadcrumb aria-label="Page navigation">
                <BreadcrumbItem href="#/pages" home>Pages</BreadcrumbItem>
                <BreadcrumbItem>{editorStore.title || 'New Page'}</BreadcrumbItem>
            </Breadcrumb>


            <div class="flex items-center gap-1">
                <button
                    onclick={() => editorStore.openSettingsDrawer()}
                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition-colors"
                    title="Page Settings"
                >
                    <CogOutline class="w-5 h-5" />
                </button>
                <button
                    onclick={() => editorStore.toggleZenMode()}
                    class="p-2 rounded-lg hover:bg-gray-100 text-gray-500 hover:text-gray-700 transition-colors"
                    title={editorStore.zenModeEnabled ? "Exit Zen Mode" : "Enter Zen Mode"}
                >
                    {#if editorStore.zenModeEnabled}
                        <WindowRestoreSolid class="w-5 h-5" />
                    {:else}
                        <WindowSolid class="w-5 h-5" />
                    {/if}
                </button>
            </div>
        </div>

        {#if !editorStore.zenModeEnabled}
             <!-- Title Input -->
            <input
                type="text"
                bind:value={editorStore.title}
                placeholder="Page Title"
                class="w-full text-3xl font-bold text-gray-800 border-none focus:ring-0 placeholder-gray-300 bg-transparent px-0"
            />
        {/if}
    </div>

    <!-- Component Toolbar (Sticky below header) -->
    <ComponentToolbar />

    <!-- Tiptap Instance -->
    <div bind:this={element} class="prose prose-lg max-w-none min-h-[500px] outline-none mx-auto"></div>

    <!-- Debug Output (Dev Only) -->
    <!-- Debug Output (Hidden by default) -->
    <!--
    <div class="bg-gray-900 text-gray-100 p-4 text-xs font-mono border-t border-gray-800">
        <div class="uppercase tracking-widest text-gray-500 mb-2">Debug Content State</div>
        {editorStore.content}
    </div>
    -->
    {/if}
</div>

<style>
    /* Scoped styles if needed, mostly using Tailwind */
    :global(.ProseMirror) {
        outline: none;
        padding: 2rem;
        min-height: 500px;
    }
</style>

<!-- Settings Drawer -->
<PageSettingsDrawer />

