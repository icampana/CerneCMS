<script>
    import FileLinesOutline from 'flowbite-svelte-icons/FileLinesOutline.svelte';
    import ImageOutline from 'flowbite-svelte-icons/ImageOutline.svelte';
    import GridSolid from 'flowbite-svelte-icons/GridSolid.svelte';
    import TextSizeOutline from 'flowbite-svelte-icons/TextSizeOutline.svelte';
    import TableColumnSolid from 'flowbite-svelte-icons/TableColumnSolid.svelte';
    import VideoCameraSolid from 'flowbite-svelte-icons/VideoCameraSolid.svelte';
    import MinusOutline from 'flowbite-svelte-icons/MinusOutline.svelte';
    import CalendarMonthSolid from 'flowbite-svelte-icons/CalendarMonthSolid.svelte';
    import BullhornSolid from 'flowbite-svelte-icons/BullhornSolid.svelte';
    import CameraPhotoSolid from 'flowbite-svelte-icons/CameraPhotoSolid.svelte';
    import ClipboardCleanSolid from 'flowbite-svelte-icons/ClipboardCleanSolid.svelte';
    import { editorStore } from '../stores/editor.svelte.js';

    const components = [
        { type: 'text', label: 'Text', icon: FileLinesOutline, action: null }, // Drag only implicitly or default
        { type: 'heading', label: 'Heading', icon: TextSizeOutline, action: null },
        { type: 'image', label: 'Image', icon: ImageOutline, action: 'image' },
        { type: 'gallery', label: 'Gallery', icon: CameraPhotoSolid, action: 'gallery' },
        { type: 'grid-2', label: '2 Col', icon: GridSolid, action: null },
        { type: 'grid-3', label: '3 Col', icon: GridSolid, action: null },
        { type: 'table', label: 'Table', icon: TableColumnSolid, action: 'table' },
        { type: 'video', label: 'Video', icon: VideoCameraSolid, action: 'video' },
        { type: 'cta', label: 'CTA', icon: BullhornSolid, action: 'cta' },
        { type: 'divider', label: 'Divider', icon: MinusOutline, action: 'divider' },
        { type: 'calendar', label: 'Calendar', icon: CalendarMonthSolid, action: 'calendar' },
        { type: 'form', label: 'Form', icon: ClipboardCleanSolid, action: 'form' },
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
            editorStore.editor.chain().focus().setImageUpload().run();
        } else if (comp.action === 'table') {
            editorStore.editor.chain().focus().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
        } else if (comp.action === 'video') {
            editorStore.openVideoModal((url) => {
                 editorStore.editor.chain().focus().setYoutubeVideo({ src: url }).run();
            });
        } else if (comp.action === 'divider') {
            editorStore.editor.chain().focus().setHorizontalRule().run();
        } else if (comp.action === 'calendar') {
             editorStore.editor.chain().focus().insertContent({ type: 'fullCalendar', attrs: { showAllCalendars: false } }).run();
        } else if (comp.action === 'cta') {
             editorStore.editor.chain().focus().insertContent({ type: 'cta' }).run();
        } else if (comp.action === 'gallery') {
             editorStore.editor.chain().focus().insertContent({ type: 'gallery' }).run();
        } else if (comp.action === 'form') {
             editorStore.editor.chain().focus().insertContent({ type: 'formBlock' }).run();
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
