<script>
    import { createEventDispatcher } from 'svelte';
    import { push } from 'svelte-spa-router';
    import DataGrid from '../DataGrid.svelte';
    import { Button } from 'flowbite-svelte';
    import { PlusOutline } from 'flowbite-svelte-icons';

    // Columns for the Page List
    const pageColumns = [
        { label: 'Title', field: 'title' },
        { label: 'Slug', field: 'slug' },
        { label: 'Status', field: 'status' },
        { label: 'Updated At', field: 'updated_at' }
    ];

    function handleEdit(page) {
        push(`/editor/${page.id}`);
    }

    function handleCreate() {
        push('/editor/new'); // Or handle creation logic
    }
</script>

<div class="bg-white rounded-xl shadow-sm border border-gray-200 min-h-[500px]">
    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
        <h2 class="text-xl font-bold">Pages</h2>
        <Button size="sm" onclick={handleCreate}>
            <PlusOutline class="w-4 h-4 mr-2" />
            New Page
        </Button>
    </div>
    <DataGrid
        endpoint="/api/pages"
        columns={pageColumns}
        onEdit={handleEdit}
    />
</div>
