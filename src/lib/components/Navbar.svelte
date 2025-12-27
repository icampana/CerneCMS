<script>
    import { Navbar, NavBrand, NavHamburger, NavUl, NavLi, Button } from 'flowbite-svelte';
    import { editorStore } from '../stores/editor.svelte.js';

    let { view, onNew, onBack } = $props();

    const handleSave = async () => {
        await editorStore.save();
    }
</script>

<Navbar class="border-b border-gray-200 bg-white sticky top-0 z-50">
    <NavBrand href="/admin">
        <span class="self-center whitespace-nowrap text-xl font-semibold dark:text-white">
            CerneCMS
        </span>
    </NavBrand>

    <div class="flex md:order-2 space-x-2">
        {#if view === 'list'}
            <Button size="sm" onclick={onNew}>New Page</Button>
        {:else if view === 'editor'}
            <div class="flex items-center mr-4 text-xs text-gray-500">
                {#if editorStore.isSaving}
                    Saving...
                {:else if editorStore.lastSaved}
                    Saved {editorStore.lastSaved.toLocaleTimeString()}
                {:else}
                    Unsaved
                {/if}
            </div>
            <Button size="sm" color="alternative" onclick={onBack}>Back</Button>
            <Button size="sm" onclick={handleSave} disabled={editorStore.isSaving}>
                Save
            </Button>
        {/if}
        <NavHamburger />
    </div>

    <NavUl>
        <NavLi href="/admin" active={view === 'list'}>Pages</NavLi>
        <!-- Future links: Media, Users, Settings -->
        <NavLi href="#" class="text-gray-400 cursor-not-allowed">Media</NavLi>
        <NavLi href="#" class="text-gray-400 cursor-not-allowed">Settings</NavLi>
    </NavUl>
</Navbar>
