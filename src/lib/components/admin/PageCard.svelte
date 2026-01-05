<script>
    import { Card, Checkbox } from 'flowbite-svelte';
    import FileOutline from 'flowbite-svelte-icons/FileOutline.svelte';
    import StatusBadge from '../ui/StatusBadge.svelte';
    import ActionMenu from '../ui/ActionMenu.svelte';

    let { page, onEdit, onDelete, onDuplicate, onPreview, isSelected, onToggleSelect } = $props();

    function handleCardClick(e) {
        // Prevent navigation when clicking controls
        if (e.target.closest('.no-click') || e.target.closest('button') || e.target.tagName === 'A') {
            return;
        }
        onEdit(page);
    }

    function formatDate(dateString) {
        if (!dateString) return 'Never';
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    }
</script>

<Card
    size="md"
    class="relative group cursor-pointer hover:shadow-lg transition-all duration-200 border border-gray-200 dark:border-gray-700 hover:border-blue-400 dark:hover:border-blue-500"
    onclick={handleCardClick}
>
    <!-- Header: Checkbox & Actions -->
    <div class="flex justify-between items-start mb-4 pl-2 pt-2">
        <div class="no-click" onclick={(e) => e.stopPropagation()} role="none">
            <Checkbox
                checked={isSelected}
                onchange={() => onToggleSelect(page.id)}
                class="accent-blue-600"
            />
        </div>
        <div class="no-click">
            <ActionMenu {page} {onEdit} {onDelete} {onDuplicate} {onPreview} />
        </div>
    </div>

    <!-- Icon -->
    <div class="flex justify-center mb-4">
        <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-xl">
            <FileOutline class="w-8 h-8 text-blue-500 dark:text-blue-400" />
        </div>
    </div>

    <!-- Content -->
    <div class="px-5 pb-5">
        <h5 class=" text-gray-900 dark:text-white truncate mb-1" title={page.title}>
            {page.title || 'Untitled Page'}
        </h5>
        <p class="text-sm text-gray-500 dark:text-gray-400 font-mono mb-4 truncate">
            /{page.slug || 'untitled'}
        </p>

        <!-- Footer Info -->

        <div class="flex items-center justify-between">
          <StatusBadge status={page.status || 'draft'} size="xs" />
          <span class="text-xs text-gray-400">
                {formatDate(page.updated_at)}
          </span>
        </div>
    </div>
</Card>
