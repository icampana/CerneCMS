<script>
    import { Button } from 'flowbite-svelte';
    import { editorStore } from '../stores/editor.svelte.js';
    import { location } from 'svelte-spa-router'; // To check if we are in editor

    const handleSave = async () => {
        await editorStore.save();
    }
</script>

{#if $location.includes('/editor')}
    <div class="fixed bottom-0 left-0 w-full z-40 border-t border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-4 transition-all duration-300 {editorStore.zenModeEnabled ? 'opacity-0 hover:opacity-100 translate-y-full hover:translate-y-0' : ''}">
        <div class="max-w-7xl mx-auto flex justify-between items-center px-4">
            <div class="text-xs md:text-sm text-gray-500 font-mono flex items-center gap-2">
                {#if editorStore.isSaving}
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    <span class="text-blue-600">Auto-saving...</span>
                {:else if editorStore.isDirty}
                    <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                    <span class="text-orange-500">Unsaved changes</span>
                {:else if editorStore.lastSaved}
                    <span class="text-gray-400">Saved {editorStore.lastSaved.toLocaleTimeString()}</span>
                {:else}
                    <span class="text-gray-400">Draft</span>
                {/if}
            </div>

            <div class="flex gap-3">
                 <Button color="alternative" href="#/pages" size="sm">
                    Back
                </Button>
                <!-- Preview button (placeholder for now) -->
                <!-- <Button color="light" size="sm">Preview</Button> -->

                <Button onclick={handleSave} disabled={editorStore.isSaving} size="sm" class="min-w-[100px]">
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
