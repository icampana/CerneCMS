<script>
    import { Button, ButtonGroup } from 'flowbite-svelte';
    import ListOutline from 'flowbite-svelte-icons/ListOutline.svelte';
    import GridSolid from 'flowbite-svelte-icons/GridSolid.svelte';

    let { viewMode = 'grid', onViewChange } = $props();

    function setView(mode) {
        onViewChange(mode);
    }

    function activeColor(mode) {
        return viewMode === mode ? 'light' : 'alternative';
    }
</script>

<div class="inline-flex items-center bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
  <ButtonGroup>
    <Button
        color={activeColor('grid')}
        size="sm"
        onclick={() => setView('grid')}
    >
        <GridSolid class="w-4 h-4" />
    </Button>
    <Button
        color={activeColor('table')}
        size="sm"
        onclick={() => setView('table')}
    >
        <ListOutline class="w-4 h-4" />
    </Button>
  </ButtonGroup>
</div>
