<script>
    import { Button } from 'flowbite-svelte';
    import { editorStore } from '../stores/editor.svelte.js';
    import { location } from 'svelte-spa-router'; // To check if we are in editor

    const handleSave = async () => {
        await editorStore.save();
    }
</script>

{#if $location.includes('/editor')}
    <div class="fixed bottom-0 left-0 w-full z-40 border-t border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 transition-transform duration-300">
        <div class="max-w-7xl mx-auto flex justify-between items-center px-4">
            <div class="text-xs md:text-sm text-gray-500 font-mono">
                {#if editorStore.isSaving}
                    <span class="animate-pulse text-blue-600">Saving...</span>
                {:else if editorStore.lastSaved}
                    Saved {editorStore.lastSaved.toLocaleTimeString()}
                {:else}
                    <span class="text-orange-500">Unsaved changes</span>
                {/if}
            </div>

            <div class="flex gap-3">
                 <Button color="alternative" href="#/pages" size="sm">
                    Back
                </Button>
                <!-- Preview button (placeholder for now) -->
                <!-- <Button color="light" size="sm">Preview</Button> -->

                <Button on:click={handleSave} disabled={editorStore.isSaving} size="sm" class="min-w-[100px]">
                    {#if editorStore.isSaving}
                        Saving...
                    {:else}
                        Save
                    {/if}
                </Button>
            </div>
        </div>
    </div>

    <!-- Padding to prevent content from being hidden behind footer -->
    <div class="h-20"></div>
{/if}
