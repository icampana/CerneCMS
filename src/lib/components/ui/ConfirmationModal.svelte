<script>
    import { Modal, Button } from 'flowbite-svelte';

    let {
        open = $bindable(false),
        title = 'Confirm Action',
        message = 'Are you sure you want to proceed?',
        confirmText = 'Confirm',
        cancelText = 'Cancel',
        onConfirm = () => {},
        onCancel = () => {},
        type = 'danger' // 'danger' | 'warning' | 'info'
    } = $props();

    function handleConfirm() {
        onConfirm();
        open = false;
    }

    function handleCancel() {
        onCancel();
        open = false;
    }

    function handleClose() {
        onCancel();
        open = false;
    }
</script>

<Modal bind:open={open} size="sm" autoclose={false} on:close={handleClose} {title}>
    <div class="flex flex-col space-y-4">
        <!-- Icon based on type -->
        <div class="flex items-center justify-center">
            {#if type === 'danger'}
                <div class="flex items-center justify-center w-16 h-16 bg-red-100 dark:bg-red-900/30 rounded-full">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            {:else if type === 'warning'}
                <div class="flex items-center justify-center w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            {:else}
                <div class="flex items-center justify-center w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            {/if}
        </div>

        <!-- Message -->
        <div class="text-center">
            <p class="text-gray-700 dark:text-gray-300">{message}</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-center gap-3 mt-4">
            <Button color="alternative" onclick={handleCancel}>{cancelText}</Button>
            <Button
                color={type === 'danger' ? 'red' : type === 'warning' ? 'yellow' : 'blue'}
                onclick={handleConfirm}
            >
                {confirmText}
            </Button>
        </div>
    </div>
</Modal>
