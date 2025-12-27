<script>
    import Editor from './lib/components/Editor.svelte';
    import DataGrid from './lib/components/DataGrid.svelte';
    import MediaManager from './lib/components/MediaManager.svelte';
    import { editorStore } from './lib/stores/editor.svelte.js';

    // State
    let view = $state('list'); // 'list' or 'editor'
    let activePageId = $state(null);

    // Columns for the Page List
    const pageColumns = [
        { label: 'Title', field: 'title' },
        { label: 'Slug', field: 'slug' },
        { label: 'Status', field: 'status' },
        { label: 'Updated At', field: 'updated_at' }
    ];

    function handleEdit(page) {
        activePageId = page.id;
        view = 'editor';
    }

    function handleCreate() {
        activePageId = null;
        view = 'editor';
    }

    function handleBack() {
        view = 'list';
        activePageId = null;
    }
</script>

<div class="cms-shell p-8 max-w-5xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold mb-2">
                {view === 'list' ? 'Pages' : (activePageId ? 'Edit Page' : 'New Page')}
            </h1>
            <p class="text-gray-600">Manage your content.</p>
        </div>

        {#if view === 'list'}
            <button
                onclick={handleCreate}
                class="px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors">
                New Page
            </button>
        {/if}
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {#if view === 'list'}
            <DataGrid
                endpoint="/api/pages"
                columns={pageColumns}
                onEdit={handleEdit}
            />
        {:else}
            {#key activePageId}
                <Editor
                    pageId={activePageId}
                    onBack={handleBack}
                />
            {/key}
        {/if}
    </div>
</div>

<!-- Global Media Library Modal -->
{#if editorStore.mediaLibraryOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
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
