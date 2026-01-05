<script>
    import { SpeedDial, SpeedDialButton } from 'flowbite-svelte';
    import PlusOutline from 'flowbite-svelte-icons/PlusOutline.svelte';

    let { actions = [], tooltip = "none" } = $props();
</script>

<SpeedDial defaultClass="fixed end-6 bottom-6 md:bottom-10" {tooltip} pill>
    {#snippet icon()}
        <PlusOutline class="w-8 h-8" />
    {/snippet}
    {#each actions as action}
        <SpeedDialButton name={action.label} onclick={action.handler}>
            {@const Icon = action.icon}
            <Icon class="w-6 h-6 text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white transition-colors" />
        </SpeedDialButton>
    {/each}
</SpeedDial>
