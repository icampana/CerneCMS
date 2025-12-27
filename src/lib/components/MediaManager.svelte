<script>
    import { onMount } from 'svelte';

    let { onSelect } = $props();

    let files = $state([]);
    let loading = $state(true);
    let uploading = $state(false);
    let fileInput;

    async function loadMedia() {
        loading = true;
        try {
            const res = await fetch('/api/media');
            if (res.ok) {
                files = await res.json();
            }
        } catch (e) {
            console.error(e);
        } finally {
            loading = false;
        }
    }

    async function handleUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        uploading = true;
        const formData = new FormData();
        formData.append('file', file);

        try {
            const res = await fetch('/api/media', {
                method: 'POST',
                body: formData
            });

            if (res.ok) {
                await loadMedia(); // Refresh list
            } else {
                alert('Upload failed');
            }
        } catch (e) {
            console.error(e);
            alert('Upload error');
        } finally {
            uploading = false;
            // Clear input
            e.target.value = '';
        }
    }

    function handleSelect(file) {
        if (onSelect) {
            onSelect(file.url);
        }
    }

    onMount(() => {
        loadMedia();
    });
</script>

<div class="bg-white rounded-lg border border-gray-200 shadow-sm flex flex-col h-[500px]">
    <!-- Header -->
    <div class="p-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
        <h3 class="font-bold text-gray-700">Media Library</h3>
        <div>
            <input
                bind:this={fileInput}
                type="file"
                accept="image/*"
                class="hidden"
                onchange={handleUpload}
            />
            <button
                onclick={() => fileInput.click()}
                disabled={uploading}
                class="px-4 py-2 bg-black text-white rounded-lg text-sm font-medium hover:bg-gray-800 disabled:opacity-50 transition-colors">
                {uploading ? 'Uploading...' : 'Upload Image'}
            </button>
        </div>
    </div>

    <!-- Grid -->
    <div class="flex-1 overflow-y-auto p-4">
        {#if loading}
            <div class="text-center text-gray-500 py-12">Loading media...</div>
        {:else if files.length === 0}
            <div class="text-center text-gray-400 py-12 border-2 border-dashed border-gray-200 rounded-lg">
                No images found. Upload one!
            </div>
        {:else}
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
                {#each files as file}
                    <button
                        class="group relative aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200 hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all"
                        onclick={() => handleSelect(file)}
                    >
                        <img
                            src={file.url}
                            alt={file.name}
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                        />
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                            <span class="text-white text-xs font-medium px-2 py-1 bg-black/70 rounded-full">
                                {onSelect ? 'Select' : file.name}
                            </span>
                        </div>
                    </button>
                {/each}
            </div>
        {/if}
    </div>
</div>
