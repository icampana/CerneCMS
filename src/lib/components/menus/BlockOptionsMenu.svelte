<script>
    import { FileCloneSolid, TrashBinSolid, CloseCircleSolid } from 'flowbite-svelte-icons';
    import { editorStore } from '../../stores/editor.svelte.js';

    let menuRef = $state(null);
    let menuStyle = $state('');

    // Update menu position when target changes
    $effect(() => {
        if (editorStore.blockMenuOpen && editorStore.blockMenuTarget) {
            const rect = editorStore.blockMenuTarget.getBoundingClientRect();
            // Position below the drag handle
            menuStyle = `top: ${rect.bottom + 5}px; left: ${rect.left}px;`;
        }
    });

    // Close menu when clicking outside
    function handleClickOutside(event) {
        if (menuRef && !menuRef.contains(event.target) &&
            editorStore.blockMenuTarget && !editorStore.blockMenuTarget.contains(event.target)) {
            editorStore.closeBlockMenu();
        }
    }

    $effect(() => {
        if (editorStore.blockMenuOpen) {
            // Delay to avoid immediate close from the click that opened it
            setTimeout(() => {
                document.addEventListener('click', handleClickOutside);
            }, 10);
        } else {
            document.removeEventListener('click', handleClickOutside);
        }
        return () => document.removeEventListener('click', handleClickOutside);
    });
</script>

{#if editorStore.blockMenuOpen}
    <div
        bind:this={menuRef}
        class="fixed z-50 w-44 bg-white rounded-lg shadow-lg border border-gray-200 py-1"
        style={menuStyle}
    >
        <button
            onclick={() => editorStore.duplicateNode()}
            class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
        >
            <FileCloneSolid class="w-4 h-4" />
            Duplicate
        </button>
        <button
            onclick={() => editorStore.clearFormatting()}
            class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-2"
        >
            <CloseCircleSolid class="w-4 h-4" />
            Clear Formatting
        </button>
        <button
            onclick={() => editorStore.deleteNode()}
            class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2"
        >
            <TrashBinSolid class="w-4 h-4" />
            Delete
        </button>
    </div>
{/if}
