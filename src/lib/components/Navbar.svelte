<script>
    import { Navbar, NavBrand, NavHamburger, NavUl, NavLi, Button } from 'flowbite-svelte';
    import { editorStore } from '../stores/editor.svelte.js';
    import { location } from 'svelte-spa-router'; // Import location store

    const handleSave = async () => {
        await editorStore.save();
    }
</script>

<Navbar class="border-b border-gray-800 bg-gray-900 sticky top-0 z-50 text-white">
    <NavBrand href="#/pages">
        <span class="self-center whitespace-nowrap text-xl font-semibold text-white">
            CerneCMS
        </span>
    </NavBrand>

    <div class="flex md:order-2 space-x-2">
        {#if $location.includes('/editor')}
            <div class="flex items-center mr-4 text-xs text-gray-400">
                {#if editorStore.isSaving}
                    Saving...
                {:else if editorStore.lastSaved}
                    Saved {editorStore.lastSaved.toLocaleTimeString()}
                {:else}
                    Unsaved
                {/if}
            </div>
            <Button size="sm" color="light" class="text-gray-900" href="#/pages">Back</Button>
            <Button size="sm" onclick={handleSave} disabled={editorStore.isSaving}>
                Save
            </Button>
        {/if}
        <NavHamburger class="text-white hover:bg-gray-800" />
    </div>

    <NavUl class="bg-gray-900 border-gray-800">
        <NavLi href="#/pages" active={$location === '/' || $location === '/pages' || $location.includes('/editor')} activeClass="text-blue-500" class="text-gray-300 hover:text-white cursor-pointer">Pages</NavLi>
        <!-- Future links: Media, Users, Settings -->
        <NavLi href="#/menus" active={$location.includes('/menus')} activeClass="text-blue-500" class="text-gray-300 hover:text-white cursor-pointer">Menus</NavLi>
        <NavLi href="#" class="text-gray-500 cursor-not-allowed">Media</NavLi>
        <NavLi href="#/settings" active={$location.includes('/settings')} activeClass="text-blue-500" class="text-gray-300 hover:text-white cursor-pointer">Settings</NavLi>
    </NavUl>
</Navbar>
