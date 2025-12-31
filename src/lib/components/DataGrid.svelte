<script>
    import { onMount } from 'svelte';
    import { Button, Checkbox } from 'flowbite-svelte';
    import StatusBadge from './ui/StatusBadge.svelte';
    import ActionMenu from './ui/ActionMenu.svelte';

    // Props
    let {
        endpoint = null,
        items = [],
        columns = [],
        onEdit,
        onDelete,
        onDuplicate,
        onPreview,
        selectedIds = [],
        onToggleSelect,
        onToggleSelectAll
    } = $props();

    // State
    let loading = $state(false);
    let error = $state(null);

    async function loadData() {
        if (!endpoint) return;
        loading = true;
        try {
            const res = await fetch(endpoint);
            if (!res.ok) throw new Error('Failed to load data');
            const data = await res.json();
            // Handle different API responses (FlightPHP might return {data: [...]})
            items = Array.isArray(data) ? data : (data.data || []);
        } catch (e) {
            error = e.message;
        } finally {
            loading = false;
        }
    }

    onMount(() => {
        if (endpoint) {
            loadData();
        }
    });

    function formatDate(dateString) {
        if (!dateString) return 'Never';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function isSelected(id) {
        return selectedIds.includes(id);
    }
</script>

{#if loading}
    <div class="p-8 text-center text-gray-500">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-4"></div>
        <p>Loading...</p>
    </div>
{:else if error}
    <div class="p-8 text-center text-red-500">
        <svg class="w-12 h-12 mx-auto mb-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <p class="font-medium">Error: {error}</p>
    </div>
{:else if items.length === 0}
    <div class="p-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No items found</h3>
        <p class="text-gray-500 dark:text-gray-400">Get started by creating your first item.</p>
    </div>
{:else}
    <!-- Table View -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    {#if onToggleSelectAll}
                        <th class="px-4 py-3 w-10">
                            <Checkbox
                                checked={selectedIds.length === items.length && items.length > 0}
                                onchange={onToggleSelectAll}
                            />
                        </th>
                    {/if}
                    {#each columns as col}
                        <th class="px-6 py-3 font-medium text-gray-500 dark:text-gray-400">{col.label}</th>
                    {/each}
                    <th class="px-6 py-3 font-medium text-gray-500 dark:text-gray-400 text-right w-24">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                {#each items as item (item.id)}
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        {#if onToggleSelectAll}
                            <td class="px-4 py-4">
                                <Checkbox
                                    checked={isSelected(item.id)}
                                    onchange={() => onToggleSelect(item.id)}
                                />
                            </td>
                        {/if}
                        {#each columns as col}
                            <td class="px-6 py-4">
                                {#if col.field === 'status'}
                                    <StatusBadge status={item[col.field] || 'draft'} size="sm" />
                                {:else if col.field === 'updated_at'}
                                    <span class="text-gray-500 dark:text-gray-400">{formatDate(item[col.field])}</span>
                                {:else if col.field === 'title' && onEdit}
                                    <button
                                        onclick={() => onEdit(item)}
                                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline font-medium cursor-pointer"
                                    >
                                        {item[col.field] || '-'}
                                    </button>
                                {:else}
                                    <span class="text-gray-900 dark:text-white">{item[col.field] || '-'}</span>
                                {/if}
                            </td>
                        {/each}
                        <td class="px-6 py-4 text-right">
                            <ActionMenu
                                page={item}
                                {onEdit}
                                {onDelete}
                                {onDuplicate}
                                {onPreview}
                            />
                        </td>
                    </tr>
                {/each}
            </tbody>
        </table>
    </div>
{/if}
