<script>
    import {
        FileLinesOutline,
        ImageOutline,
        GridSolid,
        TextSizeOutline
    } from 'flowbite-svelte-icons';

    const components = [
        { type: 'text', label: 'Text', icon: FileLinesOutline },
        { type: 'heading', label: 'Heading', icon: TextSizeOutline },
        { type: 'image', label: 'Image', icon: ImageOutline },
        { type: 'grid-2', label: '2 Col', icon: GridSolid },
        { type: 'grid-3', label: '3 Col', icon: GridSolid },
    ];

    const handleDragStart = (event, type) => {
        event.dataTransfer.setData('cerne/type', type);
        event.dataTransfer.effectAllowed = 'copy';
    };
</script>

<div class="h-16 bg-white border-b border-gray-200 flex items-center px-4 gap-4 overflow-x-auto sticky top-[65px] z-40 shadow-sm">
    {#each components as comp}
        <div
            draggable="true"
            ondragstart={(e) => handleDragStart(e, comp.type)}
            role="button"
            tabindex="0"
            aria-label="Drag {comp.label} to editor"
            class="flex flex-col items-center justify-center min-w-[60px] h-full cursor-grab hover:bg-gray-50 active:cursor-grabbing group transition-colors"
        >
            <div class="p-2 rounded-lg group-hover:bg-gray-100 transition-colors">
                <comp.icon class="w-6 h-6 text-gray-500 group-hover:text-gray-800" />
            </div>
            <span class="text-[10px] font-medium text-gray-500 uppercase tracking-wide mt-1">{comp.label}</span>
        </div>
    {/each}
</div>
