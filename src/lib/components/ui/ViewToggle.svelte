<script>
    import { Button } from 'flowbite-svelte';
    import { ListOutline, GridSolid } from 'flowbite-svelte-icons';

    let { viewMode = 'grid', onViewChange } = $props();

    function setView(mode) {
        onViewChange(mode);
    }
</script>

<div class="inline-flex items-center bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
    <Button
        color={viewMode === 'grid' ? 'default' : 'light'}
        size="sm"
        class="!px-3 !py-2"
        onclick={() => setView('grid')}
    >
        <GridSolid class="w-4 h-4" />
    </Button>
    <Button
        color={viewMode === 'table' ? 'default' : 'light'}
        size="sm"
        class="!px-3 !py-2"
        onclick={() => setView('table')}
    >
        <ListOutline class="w-4 h-4" />
    </Button>
</div>
