<script>
    import { editorStore } from '../../stores/editor.svelte.js';
    import { Modal, Label, Input, Button } from 'flowbite-svelte';

    let open = $state(false);
    let url = $state('');

    // Sync with store
    $effect(() => {
        open = editorStore.videoModalOpen;
        if (open) {
            url = ''; // Reset on open
        }
    });

    const handleConfirm = () => {
        if (url) {
            editorStore.confirmVideo(url);
        }
        close();
    };

    const close = () => {
        editorStore.closeVideoModal();
    };
</script>

<Modal bind:open={open} size="xs" autohide class="w-full" on:close={close}>
    <div class="text-center">
        <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Embed YouTube Video</h3>
        <div class="mb-6 text-left">
            <Label for="youtube-url" class="mb-2">Video URL</Label>
            <Input
                id="youtube-url"
                type="url"
                placeholder="https://www.youtube.com/watch?v=..."
                bind:value={url}
                onkeydown={(e) => e.key === 'Enter' && handleConfirm()}
            />
        </div>
        <div class="flex justify-center gap-4">
            <Button color="red" onclick={handleConfirm}>Embed Video</Button>
            <Button color="alternative" onclick={close}>Cancel</Button>
        </div>
    </div>
</Modal>
