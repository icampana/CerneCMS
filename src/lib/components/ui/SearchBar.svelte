<script>
    import { Input } from 'flowbite-svelte';
    import SearchSolid from 'flowbite-svelte-icons/SearchSolid.svelte';
    import XSolid from 'flowbite-svelte-icons/XSolid.svelte';

    let { value = '', onSearch, placeholder = 'Search pages...' } = $props();

    let localValue = $state('');
    let debounceTimer = null;

    // Sync with prop value when it changes
    $effect(() => {
        localValue = value;
    });

    function handleInput(e) {
        localValue = e.target.value;

        // Debounce search
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        debounceTimer = setTimeout(() => {
            onSearch(localValue);
        }, 300);
    }

    function handleClear() {
        localValue = '';
        onSearch('');
    }
</script>

<div class="relative w-full md:w-80">
    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
        <SearchSolid class="w-5 h-5 text-gray-400" />
    </div>
    <Input
        type="text"
        value={localValue}
        oninput={handleInput}
        {placeholder}
        class="pl-10 pr-10"
    />
    {#if localValue}
        <button
            onclick={handleClear}
            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
            type="button"
        >
            <XSolid class="w-5 h-5" />
        </button>
    {/if}
</div>
