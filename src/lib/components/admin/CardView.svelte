<script>
    import PageCard from './PageCard.svelte';

    let {
        pages = [],
        onEdit,
        onDelete,
        onDuplicate,
        onPreview,
        selectedIds = [],
        onToggleSelect
    } = $props();

    function isSelected(pageId) {
        return selectedIds.includes(pageId);
    }
</script>

{#if pages.length === 0}
    <div class="col-span-full py-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No pages found</h3>
        <p class="text-gray-500 dark:text-gray-400">Get started by creating your first page.</p>
    </div>
{:else}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {#each pages as page (page.id)}
            <PageCard
                {page}
                {onEdit}
                {onDelete}
                {onDuplicate}
                {onPreview}
                isSelected={isSelected(page.id)}
                onToggleSelect={onToggleSelect}
            />
        {/each}
    </div>
{/if}
