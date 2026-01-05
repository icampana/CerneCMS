<script>
    import { NodeViewWrapper } from 'svelte-tiptap';
    import { editorStore } from '../../stores/editor.svelte.js';
    import ImageOutline from 'flowbite-svelte-icons/ImageOutline.svelte';
    import UploadOutline from 'flowbite-svelte-icons/UploadOutline.svelte';
    import { resizeImage } from '../../utils/image.js';

    // Props from svelte-tiptap
    let { node, updateAttributes, deleteNode, editor, getPos } = $props();

    let isDragging = $state(false);
    let isUploading = $state(false);
    let uploadError = $state(null);
    let fileInput;

    function handleDragOver(e) {
        e.preventDefault();
        isDragging = true;
    }

    function handleDragLeave(e) {
        e.preventDefault();
        isDragging = false;
    }

    async function handleDrop(e) {
        e.preventDefault();
        isDragging = false;

        if (e.dataTransfer.files && e.dataTransfer.files[0]) {
            await uploadFile(e.dataTransfer.files[0]);
        }
    }

    function handleFileInput(e) {
        if (e.target.files && e.target.files[0]) {
            uploadFile(e.target.files[0]);
        }
    }

    function triggerFileInput() {
        fileInput.click();
    }

    function openMediaLibrary() {
        editorStore.openMediaLibrary((url) => {
            replaceWithImage(url);
        });
    }



    async function uploadFile(file) {
        // Validate type
        if (!file.type.startsWith('image/')) {
            uploadError = 'Please upload an image file.';
            return;
        }

        isUploading = true;
        uploadError = null;

        try {
            // Resize image if it exceeds limits (or always to fail-safe)
            const processedFile = await resizeImage(file);

            const formData = new FormData();
            formData.append('file', processedFile);

            const response = await fetch('/api/media/upload', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.error || 'Upload failed');
            }

            const data = await response.json();
            replaceWithImage(data.url);

        } catch (e) {
            console.error('Upload error:', e);
            uploadError = e.message;
            isUploading = false;
        }
    }

    function replaceWithImage(url) {
        if (editor) {
            const pos = getPos();
            editor.chain()
                .focus()
                .deleteRange({ from: pos, to: pos + 1 }) // Delete this image-upload node
                .setImage({ src: url }) // Insert the actual image node
                .run();
        }
    }
</script>

<NodeViewWrapper class="image-upload-wrapper my-4">
    <div
        class="border-2 border-dashed rounded-lg p-8 flex flex-col items-center justify-center transition-colors duration-200 relative
            {isDragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 bg-gray-50'}
            {isUploading ? 'opacity-75 pointer-events-none' : ''}"
        ondragover={handleDragOver}
        ondragleave={handleDragLeave}
        ondrop={handleDrop}
        role="button"
        tabindex="0"
    >
        {#if isUploading}
            <div class="flex flex-col items-center gap-2">
                 <!-- Flowbite Svelte doesn't have a standalone Spinner icon component usually, checking if I imported correctly or need standard SVG -->
                 <!-- Using simple SVG for spinner to be safe/consistent -->
                <svg class="animate-spin h-8 w-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-sm text-gray-500">Uploading...</p>
            </div>
        {:else}
            <div class="flex flex-col items-center gap-4 text-center">
                <div class="flex gap-4">
                     <button
                        class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-white hover:shadow-sm transition-all text-gray-500 hover:text-primary-600"
                        onclick={triggerFileInput}
                    >
                        <UploadOutline class="w-8 h-8" />
                        <span class="text-sm font-medium">Upload Image</span>
                    </button>

                    <div class="w-px bg-gray-300 h-16 self-center"></div>

                    <button
                        class="flex flex-col items-center gap-2 p-4 rounded-lg hover:bg-white hover:shadow-sm transition-all text-gray-500 hover:text-primary-600"
                        onclick={openMediaLibrary}
                    >
                        <ImageOutline class="w-8 h-8" />
                        <span class="text-sm font-medium">Select from Library</span>
                    </button>
                </div>

                <p class="text-xs text-gray-400">
                    Supports: JPG, PNG, GIF, WEBP
                </p>

                {#if uploadError}
                    <p class="text-xs text-red-500 font-medium bg-red-50 px-2 py-1 rounded">
                        {uploadError}
                    </p>
                {/if}
            </div>
        {/if}

        <input
            type="file"
            accept="image/*"
            class="hidden"
            bind:this={fileInput}
            onchange={handleFileInput}
        />
    </div>
</NodeViewWrapper>

<style>
    :global(.image-upload-wrapper) {
        display: block;
    }
</style>
