<script>
    import { NodeViewWrapper } from 'svelte-tiptap';
    import { editorStore } from '../../../stores/editor.svelte.js';
    import { CogSolid, FileLinesSolid } from 'flowbite-svelte-icons';

    let { node, updateAttributes } = $props();

    let formId = $derived(node.attrs.formId);
    let formName = $derived(node.attrs.formName);

    // In a real implementation, we might fetch form fields to preview them here.
    // For now, we'll just show the connection status.

    function openSettings(e) {
        e.stopPropagation();
        editorStore.openBlockSettings('form', node.attrs, updateAttributes);
    }
</script>

<NodeViewWrapper class="relative group my-4">
    <div class="relative w-full p-6 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 flex flex-col items-center justify-center min-h-[150px] transition-colors hover:border-blue-400">

        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity z-10 flex gap-2">
            <button
                class="bg-white/90 p-2 rounded-full shadow-lg hover:bg-white text-gray-700"
                onclick={openSettings}
                title="Form Settings"
            >
                <CogSolid class="w-5 h-5" />
            </button>
        </div>

        {#if formId}
             <div class="text-center">
                <FileLinesSolid class="w-10 h-10 text-blue-500 mx-auto mb-3" />
                <h3 class="text-lg font-semibold text-gray-800">{formName}</h3>
                <p class="text-sm text-gray-500">Form ID: {formId}</p>
                <div class="mt-4 px-4 py-2 bg-blue-100 text-blue-700 text-xs rounded-full font-medium inline-block">
                    Ready to render
                </div>
            </div>
        {:else}
            <div class="text-center">
                <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-3">
                    <FileLinesSolid class="w-6 h-6 text-gray-400" />
                </div>
                <h3 class="font-medium text-gray-600">Select a Form</h3>
                <p class="text-sm text-gray-400 mt-1">Open settings to choose or create a form</p>
                <button
                    onclick={openSettings}
                    class="mt-4 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-md shadow-sm text-sm hover:bg-gray-50"
                >
                    Configure Form
                </button>
            </div>
        {/if}
    </div>
</NodeViewWrapper>
