<script>
    import Router from 'svelte-spa-router';
    // Layout
    // import Navbar from './lib/components/Navbar.svelte'; // Deprecated
    import NavigationDrawer from './lib/components/NavigationDrawer.svelte';
    import StickyActionBar from './lib/components/StickyActionBar.svelte';
    import { Button } from 'flowbite-svelte';
    import { BarsFromLeftOutline } from 'flowbite-svelte-icons';

    import MediaManager from './lib/components/MediaManager.svelte';
    import VideoModal from './lib/components/modals/VideoModal.svelte';
    import { Toast } from 'flowbite-svelte';
    import { CheckCircleSolid, CloseCircleSolid } from 'flowbite-svelte-icons';
    import { editorStore } from './lib/stores/editor.svelte.js';
    import BlockSettingsDrawer from './lib/components/drawers/BlockSettingsDrawer.svelte';

    // Routes
    import PageList from './lib/components/admin/PageList.svelte';
    import Editor from './lib/components/Editor.svelte';
    import MenuManager from './lib/components/admin/MenuManager.svelte';
    import SiteSettings from './lib/components/admin/SiteSettings.svelte';

    const routes = {
        '/': PageList,
        '/pages': PageList,
        '/editor/:pageId': Editor,
        '/menus': MenuManager,
        '/settings': SiteSettings
    };
</script>

<div class="min-h-screen bg-gray-50 flex flex-col relative">

    <!-- Navigation Drawer -->
    <NavigationDrawer />

    <!-- Hamburger Trigger (Fixed top-left) -->
    {#if !editorStore.zenModeEnabled}
    <div class="fixed top-4 left-4 z-30 print:hidden">
        <Button color="light" class="!p-2 shadow-sm bg-white/80 backdrop-blur" onclick={(e) => { e.stopPropagation(); editorStore.toggleNavDrawer(); }}>
            <BarsFromLeftOutline class="w-6 h-6 text-gray-600" />
        </Button>
    </div>
    {/if}

    <!-- Main Content Area -->
    <!-- Added pt-14 to account for trigger button space if needed, or just let it float over -->
    <div class="cms-shell flex-1 p-8 pt-16 max-w-7xl mx-auto w-full transition-all duration-300">
        <!-- Router View -->
        <Router {routes} />
    </div>

    <!-- Sticky Action Bar (Only shows in editor) -->
    <StickyActionBar />
</div>

<!-- Global Media Library Modal -->
{#if editorStore.mediaLibraryOpen}
    <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-lg">Select Image</h3>
                <button onclick={() => editorStore.closeMediaLibrary()} class="p-2 hover:bg-gray-100 rounded-full">
                    ✕
                </button>
            </div>
            <MediaManager
                onSelect={(url) => editorStore.selectMedia(url)}
            />
        </div>
    </div>
{/if}

<!-- Global Video Modal -->
{#if editorStore.videoModalOpen}
    <VideoModal />
{/if}

<!-- Global Toast Notification -->
{#if editorStore.toastVisible}
    <div class="fixed bottom-4 right-4 z-[100]">
        <Toast
            color={editorStore.toastType === 'success' ? 'green' : 'red'}
            dismissable
            onclick={() => editorStore.hideToast()}
        >
            {#snippet icon()}
                {#if editorStore.toastType === 'success'}
                    <CheckCircleSolid class="w-5 h-5" />
                {:else}
                    <CloseCircleSolid class="w-5 h-5" />
                {/if}
            {/snippet}
            {editorStore.toastMessage}
            {#if editorStore.undoCallback}
                <button
                    onclick={(e) => { e.stopPropagation(); editorStore.undoAction(); }}
                    class="ml-3 text-sm font-medium text-blue-600 hover:underline dark:text-blue-500"
                >
                    Undo
                </button>
            {/if}
        </Toast>
    </div>
{/if}

<!-- Block Settings Drawer -->
<BlockSettingsDrawer />
