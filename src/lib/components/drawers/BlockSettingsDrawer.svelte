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
    width="w-80"
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
    {:else}
        <div class="text-gray-500 text-sm">
            Select a block to edit its settings.
        </div>
    {/if}
</Drawer>
