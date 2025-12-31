<script>
    import { Dropdown, DropdownItem, Button, Badge } from 'flowbite-svelte';
    import { ChevronDownOutline, FilterSolid } from 'flowbite-svelte-icons';

    let { value = 'all', onChange, counts = {} } = $props();

    const filterOptions = [
        { id: 'all', label: 'All Pages' },
        { id: 'published', label: 'Published' },
        { id: 'draft', label: 'Draft' }
    ];

    function handleSelect(filterId) {
        onChange(filterId);
    }

    function getCount(filterId) {
        return counts[filterId] || 0;
    }
</script>

<Button>Filter By<ChevronDownOutline class="ms-2 h-6 w-6 text-white dark:text-white" /></Button>
<Dropdown dismissable={true}>
    <Button slot="trigger" color="light" class="flex items-center gap-2">
        <FilterSolid class="w-4 h-4" />
        <span class="hidden sm:inline">
            {filterOptions.find(f => f.id === value)?.label || 'Filter'}
        </span>
        {#if getCount(value) > 0}
            <Badge color="gray" size="sm">{getCount(value)}</Badge>
        {/if}
    </Button>
    <ul class="py-1 text-sm text-gray-700 dark:text-gray-200 min-w-40">
        {#each filterOptions as option}
            <DropdownItem
                onclick={() => handleSelect(option.id)}
                class={value === option.id ? 'bg-blue-50 dark:bg-blue-900/20' : ''}
            >
                <div class="flex items-center justify-between w-full">
                    <span>{option.label}</span>
                    {#if getCount(option.id) > 0}
                        <Badge color="light" size="sm">{getCount(option.id)}</Badge>
                    {/if}
                </div>
            </DropdownItem>
        {/each}
    </ul>
</Dropdown>
