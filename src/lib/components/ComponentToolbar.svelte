<script>
    import {
        FileLinesOutline,
        ImageOutline,
        GridSolid,
        TextSizeOutline,
        TableColumnSolid,
        VideoCameraSolid,
        MinusOutline
    } from 'flowbite-svelte-icons';
    import { editorStore } from '../stores/editor.svelte.js';

    const components = [
        { type: 'text', label: 'Text', icon: FileLinesOutline, action: null }, // Drag only implicitly or default
        { type: 'heading', label: 'Heading', icon: TextSizeOutline, action: null },
        { type: 'image', label: 'Image', icon: ImageOutline, action: 'image' },
        { type: 'grid-2', label: '2 Col', icon: GridSolid, action: null },
        { type: 'grid-3', label: '3 Col', icon: GridSolid, action: null },
        { type: 'table', label: 'Table', icon: TableColumnSolid, action: 'table' },
        { type: 'video', label: 'Video', icon: VideoCameraSolid, action: 'video' },
        { type: 'divider', label: 'Divider', icon: MinusOutline, action: 'divider' },
    ];

    const handleDragStart = (event, type) => {
        event.dataTransfer.setData('cerne/type', type);
        event.dataTransfer.effectAllowed = 'copy';
    };

    const handleClick = (comp) => {
        if (!editorStore.editor) return;

        // Focus editor first
        editorStore.editor.chain().focus();

        if (comp.action === 'image') {
            editorStore.openMediaLibrary((url) => {
                 editorStore.editor.chain().focus().setImage({ src: url }).run();
            });
        } else if (comp.action === 'table') {
            editorStore.editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
        } else if (comp.action === 'video') {
            editorStore.openVideoModal((url) => {
                 editorStore.editor.chain().focus().setYoutubeVideo({ src: url }).run();
            });
        } else if (comp.action === 'divider') {
            editorStore.editor.chain().focus().setHorizontalRule().run();
        }
    };
</script>

<div class="h-16 bg-white border-b border-gray-200 flex items-center px-4 gap-4 overflow-x-auto sticky top-[65px] z-40 shadow-sm">
    {#each components as comp}
        <div
            draggable="true"
            ondragstart={(e) => handleDragStart(e, comp.type)}
            onclick={() => handleClick(comp)}
            role="button"
            tabindex="0"
            onkeydown={(e) => e.key === 'Enter' && handleClick(comp)}
            aria-label="Insert {comp.label}"
            class="flex flex-col items-center justify-center min-w-[60px] h-full cursor-pointer hover:bg-gray-50 active:bg-gray-100 group transition-colors"
        >
            <div class="p-2 rounded-lg group-hover:bg-gray-100 transition-colors">
                <comp.icon class="w-6 h-6 text-gray-500 group-hover:text-gray-800" />
            </div>
            <span class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-1">{comp.label}</span>
        </div>
    {/each}
</div>
