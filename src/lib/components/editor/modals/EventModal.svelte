<script>
    import { createEventDispatcher } from 'svelte';
    import DatePicker from '../../ui/DatePicker.svelte';

    export let event = {
        title: '',
        start: '',
        end: '',
        url: '',
        description: '',
        image: '',
        is_visible: true,
        color: '#3788d8'
    };
    export let isOpen = false;

    const dispatch = createEventDispatcher();

    function close() {
        dispatch('close');
    }

    function save() {
        dispatch('save', event);
    }

    function remove() {
        if(confirm('Are you sure you want to delete this event?')) {
             dispatch('delete', event);
        }
    }
</script>

{#if isOpen}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md p-6 max-h-screen overflow-y-auto">
            <h2 class="text-xl font-bold mb-4 dark:text-white">{event.id ? 'Edit Event' : 'Add Event'}</h2>

            <form on:submit|preventDefault={save}>
                <div class="mb-4">
                    <label for="title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Title</label>
                    <input type="text" id="title" bind:value={event.title} required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                </div>

                <DatePicker id="start" label="Start Date" bind:value={event.start} required />
                <DatePicker id="end" label="End Date" bind:value={event.end} />

                <div class="mb-4">
                    <label for="url" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">URL</label>
                    <input type="url" id="url" bind:value={event.url} class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white" />
                </div>

                <div class="mb-4">
                    <label for="color" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Color</label>
                    <input type="color" id="color" bind:value={event.color} class="h-10 w-full" />
                </div>

                <div class="mb-4 flex items-center">
                    <input type="checkbox" id="visible" bind:checked={event.is_visible} class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600" />
                    <label for="visible" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Visible</label>
                </div>

                <div class="flex justify-end gap-2 mt-6">
                    {#if event.id}
                        <button type="button" on:click={remove} class="text-red-600 hover:text-red-800 font-medium text-sm px-5 py-2.5">Delete</button>
                    {/if}
                    <button type="button" on:click={close} class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">Cancel</button>
                    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Save</button>
                </div>
            </form>
        </div>
    </div>
{/if}
