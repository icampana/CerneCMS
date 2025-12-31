<script>
    import { Dropdown, DropdownItem, Button } from 'flowbite-svelte';
    import { PenOutline, TrashBinOutline, EyeOutline } from 'flowbite-svelte-icons';

    let { page, onEdit, onDelete, onDuplicate, onPreview } = $props();

    let isOpen = $state(false);

    function handleEdit() {
        isOpen = false;
        onEdit(page);
    }

    function handleDuplicate() {
        isOpen = false;
        onDuplicate(page);
    }

    function handleDelete() {
        isOpen = false;
        if (confirm(`Are you sure you want to delete "${page.title}"?`)) {
            onDelete(page);
        }
    }

    function handlePreview() {
        isOpen = false;
        if (onPreview) {
            onPreview(page);
        }
    }
</script>

<Dropdown placement="bottom-end" dismissable={true} bind:open={isOpen}>
    <Button slot="trigger" color="light" class="!p-2 hover:bg-gray-100 dark:hover:bg-gray-700">
        <EllipsisVerticalOutline class="w-5 h-5 text-gray-500" />
    </Button>
    <ul class="py-1 text-sm text-gray-700 dark:text-gray-200">
        <DropdownItem onclick={handleEdit}>
            <div class="flex items-center gap-2">
                <PenOutline class="w-4 h-4" />
                <span>Edit</span>
            </div>
        </DropdownItem>
        <DropdownItem onclick={handleDuplicate}>
            <div class="flex items-center gap-2">
                <DuplicateOutline class="w-4 h-4" />
                <span>Duplicate</span>
            </div>
        </DropdownItem>
        {#if onPreview}
            <DropdownItem onclick={handlePreview}>
                <div class="flex items-center gap-2">
                    <EyeOutline class="w-4 h-4" />
                    <span>Preview</span>
                </div>
            </DropdownItem>
        {/if}
        <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
        <DropdownItem onclick={handleDelete} class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
            <div class="flex items-center gap-2">
                <TrashBinOutline class="w-4 h-4" />
                <span>Delete</span>
            </div>
        </DropdownItem>
    </ul>
</Dropdown>
