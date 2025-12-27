<script>
    import { onMount } from 'svelte';

    // Props
    let { endpoint, columns = [], onEdit, onDelete } = $props();

    // State
    let items = $state([]);
    let loading = $state(true);
    let error = $state(null);

    async function loadData() {
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
        loadData();
    });
</script>

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    {#if loading}
        <div class="p-8 text-center text-gray-500">Loading...</div>
    {:else if error}
        <div class="p-8 text-center text-red-500">Error: {error}</div>
    {:else if items.length === 0}
        <div class="p-8 text-center text-gray-500">No items found.</div>
    {:else}
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    {#each columns as col}
                        <th class="px-6 py-3 font-medium text-gray-500">{col.label}</th>
                    {/each}
                    <th class="px-6 py-3 font-medium text-gray-500 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                {#each items as item}
                    <tr class="hover:bg-gray-50">
                        {#each columns as col}
                            <td class="px-6 py-4">
                                {#if col.field === 'status'}
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        {item[col.field] === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'}">
                                        {item[col.field]}
                                    </span>
                                {:else}
                                    {item[col.field]}
                                {/if}
                            </td>
                        {/each}
                        <td class="px-6 py-4 text-right space-x-2">
                             <button
                                onclick={() => onEdit(item)}
                                class="text-blue-600 hover:text-blue-800 font-medium">
                                Edit
                            </button>
                            <!-- Delete not implemented in API yet, but UI is ready -->
                             <button
                                onclick={() => onDelete && onDelete(item)}
                                class="text-red-400 hover:text-red-600">
                                Delete
                            </button>
                        </td>
                    </tr>
                {/each}
            </tbody>
        </table>
    {/if}
</div>
