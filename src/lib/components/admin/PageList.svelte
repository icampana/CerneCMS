<script>
    import { onMount } from 'svelte';
    import { push } from 'svelte-spa-router';
    import { Button, ButtonGroup } from 'flowbite-svelte';
    import PlusOutline from 'flowbite-svelte-icons/PlusOutline.svelte';
    import TrashBinOutline from 'flowbite-svelte-icons/TrashBinOutline.svelte';

    import CardView from './CardView.svelte';
    import DataGrid from '../DataGrid.svelte';
    import ViewToggle from '../ui/ViewToggle.svelte';
    import SearchBar from '../ui/SearchBar.svelte';
    import FilterDropdown from '../ui/FilterDropdown.svelte';
    import ConfirmationModal from '../ui/ConfirmationModal.svelte';

    import { pagesStore } from '../../stores/pagesStore.svelte.js';

    // Columns for the Table View
    const pageColumns = [
        { label: 'Title', field: 'title' },
        { label: 'Slug', field: 'slug' },
        { label: 'Status', field: 'status' },
        { label: 'Updated At', field: 'updated_at' }
    ];

    // Load pages on mount
    onMount(() => {
        pagesStore.loadPages();
        pagesStore.loadViewModePreference();
    });

    // Handlers
    function handleEdit(page) {
        pagesStore.editPage(page);
    }

    function handleCreate() {
        push('/editor/new');
    }

    function handleDelete(page) {
        pagesStore.deletePage(page);
    }

    function handleDuplicate(page) {
        pagesStore.duplicatePage(page);
    }

    function handlePreview(page) {
        pagesStore.previewPage(page);
    }

    function handleBulkDelete() {
        if (pagesStore.selectedIds.length === 0) return;

        const count = pagesStore.selectedIds.length;
        if (confirm(`Are you sure you want to delete ${count} page(s)?`)) {
            pagesStore.deletePages(pagesStore.selectedIds);
        }
    }

    function handleSearch(query) {
        pagesStore.setSearch(query);
    }

    function handleFilter(status) {
        pagesStore.setStatusFilter(status);
    }

    function handleViewChange(mode) {
        pagesStore.toggleViewMode(mode);
    }

    function handleToggleSelect(id) {
        pagesStore.toggleSelection(id);
    }

    function handleToggleSelectAll() {
        pagesStore.toggleSelectAll();
    }
</script>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 min-h-[500px]">
    <!-- Header -->
    <div class="p-4 md:p-6 border-b border-gray-100 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Title -->
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pages</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {pagesStore.filteredPages.length} page{pagesStore.filteredPages.length !== 1 ? 's' : ''}
                </p>
            </div>

            <!-- Search -->
            <SearchBar
                value={pagesStore.searchQuery}
                onSearch={handleSearch}
                placeholder="Search pages..."
            />

            <!-- Filter -->
            <FilterDropdown
                value={pagesStore.statusFilter}
                onChange={handleFilter}
                counts={pagesStore.statusCounts}
            />

                        <!-- Bulk Actions (shown when items selected) -->
            {#if pagesStore.selectedIds.length > 0}
                <div class="flex items-center gap-2 animate-fade-in">
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        {pagesStore.selectedIds.length} selected
                    </span>

                    <Button
                        size="sm"
                        color="red"
                        onclick={handleBulkDelete}
                        class="px-3!"
                    >
                        <TrashBinOutline class="w-4 h-4" />
                    </Button>
                    <Button
                        size="sm"
                        color="light"
                        onclick={() => pagesStore.clearSelection()}
                        class="px-3!"
                    >
                        Clear
                    </Button>
                </div>
            {:else}
                <!-- View Toggle -->
                <ViewToggle
                    viewMode={pagesStore.viewMode}
                    onViewChange={handleViewChange}
                />
            {/if}

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <Button size="sm" onclick={handleCreate}>
                    <PlusOutline class="w-4 h-4 mr-2" />
                    New Page
                </Button>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mt-4">



        </div>
    </div>



    <!-- Content -->
    <div class="p-4 md:p-6">
        {#if pagesStore.loading}
            <div class="flex items-center justify-center py-12">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            </div>
        {:else if pagesStore.error}
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <svg class="w-12 h-12 text-red-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Error loading pages</h3>
                <p class="text-gray-500 dark:text-gray-400">{pagesStore.error}</p>
            </div>
        {:else if pagesStore.filteredPages.length === 0}
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No pages found</h3>
                <p class="text-gray-500 dark:text-gray-400">
                    {#if pagesStore.searchQuery || pagesStore.statusFilter !== 'all'}
                        Try adjusting your search or filter
                    {:else}
                        Get started by creating your first page
                    {/if}
                </p>
            </div>
        {:else}
            <!-- Grid View (Card) -->
            {#if pagesStore.viewMode === 'grid'}
                <CardView
                    pages={pagesStore.filteredPages}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    onDuplicate={handleDuplicate}
                    onPreview={handlePreview}
                    selectedIds={pagesStore.selectedIds}
                    onToggleSelect={handleToggleSelect}
                />
            <!-- Table View -->
            {:else}
                <DataGrid
                    items={pagesStore.filteredPages}
                    columns={pageColumns}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    onDuplicate={handleDuplicate}
                    onPreview={handlePreview}
                    viewMode="table"
                    selectedIds={pagesStore.selectedIds}
                    onToggleSelect={handleToggleSelect}
                    onToggleSelectAll={handleToggleSelectAll}
                />
            {/if}
        {/if}
    </div>
</div>

<!-- Confirmation Modal -->
<ConfirmationModal
    bind:open={pagesStore.confirmModalOpen}
    title={pagesStore.confirmModalConfig.title}
    message={pagesStore.confirmModalConfig.message}
    type={pagesStore.confirmModalConfig.type}
    loading={pagesStore.deleteLoading}
    onConfirm={() => pagesStore.handleModalConfirm()}
    onCancel={() => pagesStore.handleModalCancel()}
    confirmText="Delete"
    cancelText="Cancel"
/>

<style>
    @keyframes fade-in {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in {
        animation: fade-in 0.2s ease-out;
    }
</style>
