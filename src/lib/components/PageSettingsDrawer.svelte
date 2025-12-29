<script>
    import { Drawer, Button, Label, Input, Textarea } from 'flowbite-svelte';
    import { CogOutline } from 'flowbite-svelte-icons';
    import { editorStore } from '../stores/editor.svelte.js';

    function handleSlugChange(event) {
        editorStore.slug = event.target.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
</script>

<Drawer
    bind:open={editorStore.settingsDrawerOpen}
    placement="right"
    width="w-96"
    class="bg-white dark:bg-gray-800 p-0"
    on:close={() => editorStore.closeSettingsDrawer()}
>
    <div class="flex flex-col h-full">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h5 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                <CogOutline class="w-5 h-5" />
                Page Settings
            </h5>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4 space-y-6">
            <!-- Slug Section -->
            <div>
                <Label for="slug" class="mb-2 font-semibold text-gray-700 dark:text-gray-300">URL Slug</Label>
                <div class="flex items-center gap-2">
                    <span class="text-gray-500 text-sm">/</span>
                    <Input
                        id="slug"
                        type="text"
                        value={editorStore.slug || ''}
                        oninput={handleSlugChange}
                        placeholder="page-url-slug"
                        class="flex-1"
                    />
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    The URL path for this page. Leave empty to auto-generate from title.
                </p>
            </div>

            <!-- Hierarchy & Navigation -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                 <h6 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">Structure & Navigation</h6>

                 <div class="space-y-4">
                    <div>
                        <Label class="mb-2">Parent Page ID</Label>
                        <div class="flex gap-2">
                            <Input
                                type="number"
                                bind:value={editorStore.parentId}
                                placeholder="Root (No Parent)"
                            />
                            <!-- TODO: Replace with InternalLinkPicker when better integrated/styled for this context -->
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Enter the ID of the parent page to nest this page under it.
                        </p>
                    </div>

                    <div>
                        <Label class="mb-2">Sidebar Visibility</Label>
                        <select
                            bind:value={editorStore.metaJson.sidebar_override}
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
                        >
                            <option value={undefined}>Inherit (Default)</option>
                            <option value="show">Always Show</option>
                            <option value="hide">Always Hide</option>
                        </select>
                         <p class="text-xs text-gray-500 mt-1">
                            Override the global sidebar setting for this specific page.
                        </p>
                    </div>
                 </div>
            </div>

            <!-- SEO Section -->
            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h6 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">SEO Settings</h6>

                <div class="space-y-4">
                    <div>
                        <Label for="metaTitle" class="mb-2">Meta Title</Label>
                        <Input
                            id="metaTitle"
                            type="text"
                            bind:value={editorStore.metaTitle}
                            placeholder="Page title for search engines"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Recommended: 50-60 characters. {editorStore.metaTitle?.length || 0}/60
                        </p>
                    </div>

                    <div>
                        <Label for="metaDescription" class="mb-2">Meta Description</Label>
                        <Textarea
                            id="metaDescription"
                            rows="3"
                            bind:value={editorStore.metaDescription}
                            placeholder="Brief description for search results"
                            class="w-full"
                        />
                        <p class="text-xs text-gray-500 mt-1">
                            Recommended: 150-160 characters. {editorStore.metaDescription?.length || 0}/160
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            <Button onclick={() => editorStore.closeSettingsDrawer()} class="w-full">
                Done
            </Button>
        </div>
    </div>
</Drawer>
