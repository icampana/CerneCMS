<script>
    import { Drawer, Label, Input, Checkbox, CloseButton } from 'flowbite-svelte';
    import { sineIn } from 'svelte/easing';
    import { editorStore } from '../../stores/editor.svelte.js';

    // Derive open state directly from store (one-way)
    let drawerOpen = $derived(editorStore.blockSettings.isOpen);

    let transitionParams = {
        x: 320,
        duration: 200,
        easing: sineIn
    };

    function closeDrawer() {
        console.log('Closing drawer');
        editorStore.closeBlockSettings();
    }

    function update(key, value) {
        console.log('Updating', key, value);
        editorStore.updateBlockSetting(key, value);
    }

    function onCheckboxChange(e) {
        const checked = e.target.checked;
        console.log('Checkbox changed to:', checked);
        update('lightbox', checked);
    }
</script>

<Drawer
    placement="right"
    transitionType="fly"
    {transitionParams}
    open={drawerOpen}
    dismissable={false}
    id="block-settings-drawer"
    width="w-96"
>
    <div class="flex items-center justify-between mb-6">
        <h5 class="text-base font-semibold text-gray-500 uppercase dark:text-gray-400">
            Block Settings
        </h5>
        <CloseButton onclick={closeDrawer} class="dark:text-white" />
    </div>

    {#if editorStore.blockSettings.type === 'image'}
        <div class="space-y-6">
            <div>
                <Label class="mb-2">Caption/Title</Label>
                <Input
                    type="text"
                    value={editorStore.blockSettings.attributes.title || ''}
                    oninput={(e) => update('title', e.target.value)}
                    placeholder="Enter image caption"
                />
            </div>

            <div>
                <Label class="mb-2">Alt Text</Label>
                <Input
                    type="text"
                    value={editorStore.blockSettings.attributes.alt || ''}
                    oninput={(e) => update('alt', e.target.value)}
                    placeholder="Describe image for screen readers"
                />
            </div>

            <div class="pt-4 border-t border-gray-100">
                <Checkbox
                    checked={editorStore.blockSettings.attributes.lightbox || false}
                    onchange={onCheckboxChange}
                >
                    Allow Zooming into the Image
                </Checkbox>
                <p class="text-xs text-gray-400 mt-2 ml-6">
                    When enabled, clicking the image will open it in a fullscreen gallery view.
                </p>
            </div>
        </div>
    {:else if editorStore.blockSettings.type === 'cta'}
        <div class="space-y-6">
            <div>
                <Label class="mb-2">Layout</Label>
                <div class="flex gap-2">
                    <button
                        class="flex-1 p-2 border rounded hover:bg-gray-50 dark:hover:bg-gray-700 {editorStore.blockSettings.attributes.layout === 'centered' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'}"
                        onclick={() => update('layout', 'centered')}
                        title="Centered"
                    >
                        <div class="space-y-1">
                            <div class="h-1 bg-gray-400 w-1/2 mx-auto rounded"></div>
                            <div class="h-1 bg-gray-300 w-3/4 mx-auto rounded"></div>
                        </div>
                    </button>
                    <button
                        class="flex-1 p-2 border rounded hover:bg-gray-50 dark:hover:bg-gray-700 {editorStore.blockSettings.attributes.layout === 'split' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'}"
                         onclick={() => update('layout', 'split')}
                         title="Split"
                    >
                        <div class="flex gap-2">
                            <div class="w-1/2 space-y-1">
                                <div class="h-1 bg-gray-400 w-full rounded"></div>
                                <div class="h-1 bg-gray-300 w-3/4 rounded"></div>
                            </div>
                            <div class="w-1/2 flex items-center justify-center">
                                <div class="h-2 w-8 bg-gray-400 rounded"></div>
                            </div>
                        </div>
                    </button>
                    <button
                        class="flex-1 p-2 border rounded hover:bg-gray-50 dark:hover:bg-gray-700 {editorStore.blockSettings.attributes.layout === 'hero' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'}"
                         onclick={() => update('layout', 'hero')}
                         title="Hero"
                    >
                        <div class="h-8 bg-gray-200 w-full rounded flex items-center justify-center">
                             <div class="h-1 bg-gray-400 w-1/2 rounded"></div>
                        </div>
                    </button>
                </div>
            </div>

            <div>
                 <Label class="mb-2">Button Link</Label>
                 <Input
                    type="text"
                    value={editorStore.blockSettings.attributes.buttonUrl || '#'}
                    oninput={(e) => update('buttonUrl', e.target.value)}
                    placeholder="https://"
                />
            </div>

            <div>
                <Label class="mb-2">Text Color</Label>
                <div class="flex gap-2">
                    <button
                        class="flex-1 p-3 border rounded hover:bg-gray-50 dark:hover:bg-gray-700 {editorStore.blockSettings.attributes.textColor === 'auto' || !editorStore.blockSettings.attributes.textColor ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'}"
                        onclick={() => update('textColor', 'auto')}
                        title="Auto"
                    >
                        <div class="text-sm font-medium">Auto</div>
                    </button>
                    <button
                        class="flex-1 p-3 border rounded hover:bg-gray-50 dark:hover:bg-gray-700 {editorStore.blockSettings.attributes.textColor === 'light' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'}"
                        onclick={() => update('textColor', 'light')}
                        title="Light"
                    >
                        <div class="h-4 w-4 bg-white border border-gray-300 rounded mx-auto"></div>
                        <div class="text-xs mt-1">Light</div>
                    </button>
                    <button
                        class="flex-1 p-3 border rounded hover:bg-gray-50 dark:hover:bg-gray-700 {editorStore.blockSettings.attributes.textColor === 'dark' ? 'border-blue-500 ring-1 ring-blue-500' : 'border-gray-200'}"
                        onclick={() => update('textColor', 'dark')}
                        title="Dark"
                    >
                        <div class="h-4 w-4 bg-gray-900 rounded mx-auto"></div>
                        <div class="text-xs mt-1">Dark</div>
                    </button>
                </div>
            </div>

            <div>
                <Label class="mb-2">Background Image</Label>
                {#if editorStore.blockSettings.attributes.backgroundImage}
                    <div class="mb-2 relative group">
                        <img src={editorStore.blockSettings.attributes.backgroundImage} alt="Background" class="w-full h-32 object-cover rounded-lg border border-gray-200" />
                        <button
                            class="absolute top-2 right-2 bg-red-500 text-white p-1 rounded-full shadow hover:bg-red-600 opacity-0 group-hover:opacity-100 transition-opacity"
                            onclick={() => update('backgroundImage', '')}
                            title="Remove Image"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                     <button class="text-sm text-blue-600 hover:underline" onclick={() => editorStore.openMediaLibrary((url) => update('backgroundImage', url))}>
                        Change Image
                    </button>
                {:else}
                    <div
                        class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:bg-gray-50 cursor-pointer"
                        onclick={() => editorStore.openMediaLibrary((url) => update('backgroundImage', url))}
                    >
                         <div class="text-gray-400 mb-2">
                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                         </div>
                         <span class="text-sm text-gray-500">Select Background Image</span>
                    </div>
                {/if}
            </div>
        </div>
    {:else}
        <div class="text-gray-500 text-sm">
            Select a block to edit its settings.
        </div>
    {/if}
</Drawer>
