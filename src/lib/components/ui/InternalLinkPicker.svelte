<script>
  import { createEventDispatcher } from 'svelte';
  import { onMount } from 'svelte';

  export let selectedPageId = null;
  export let placeholder = "Search for a page...";

  const dispatch = createEventDispatcher();

  let searchTerm = '';
  let results = [];
  let isSearching = false;
  let showDropdown = false;
  let selectedPage = null;
  let debounceTimer;

  function handleInput() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      searchPages();
    }, 300);
  }

  async function searchPages() {
    if (searchTerm.length < 2) {
      results = [];
      return;
    }

    isSearching = true;
    try {
      const res = await fetch(`/api/pages/search?q=${encodeURIComponent(searchTerm)}`);
      if (res.ok) {
        results = await res.json();
        showDropdown = true;
      }
    } catch (e) {
      console.error('Search failed', e);
    } finally {
      isSearching = false;
    }
  }

  function selectPage(page) {
    selectedPage = page;
    selectedPageId = page.id;
    searchTerm = page.title;
    showDropdown = false;
    dispatch('select', page);
  }

  // If initial ID provided, try to fetch it to show title (optional optimization)
</script>

<div class="relative w-full">
  <div class="relative">
    <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
        <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
        </svg>
    </div>
    <input type="text"
           bind:value={searchTerm}
           on:input={handleInput}
           on:focus={() => { if(results.length) showDropdown = true; }}
           on:blur={() => setTimeout(() => showDropdown = false, 200)}
           class="block w-full p-2.5 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500"
           {placeholder} />
    {#if isSearching}
        <div class="absolute inset-y-0 end-0 flex items-center pe-3">
             <div class="animate-spin h-4 w-4 border-2 border-blue-500 rounded-full border-t-transparent"></div>
        </div>
    {/if}
  </div>

  {#if showDropdown && results.length > 0}
    <div class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
      <ul class="py-1">
        {#each results as page}
          <li>
            <button type="button"
                    on:click={() => selectPage(page)}
                    class="w-full text-left px-4 py-2 hover:bg-gray-100 flex flex-col">
              <span class="text-sm font-medium text-gray-900">{page.title}</span>
              <span class="text-xs text-gray-500">/{page.slug}</span>
            </button>
          </li>
        {/each}
      </ul>
    </div>
  {/if}
</div>
