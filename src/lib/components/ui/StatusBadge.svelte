<script>
    import { Badge } from 'flowbite-svelte';
    import { CheckCircleSolid, ClockSolid, ArchiveSolid, TrashBinSolid } from 'flowbite-svelte-icons';

    let { status = 'draft', size = 'large' } = $props();

    const statusConfig = {
        published: {
            color: 'green',
            label: 'Published',
            icon: CheckCircleSolid
        },
        draft: {
            color: 'yellow',
            label: 'Draft',
            icon: ClockSolid
        },
        archived: {
            color: 'purple',
            label: 'Archived',
            icon: ArchiveSolid
        },
        deleted: {
            color: 'red',
            label: 'Deleted',
            icon: TrashBinSolid
        }
    };

    let config = $derived(statusConfig[status] || statusConfig.draft);
    let IconComponent = $derived(config.icon);
</script>

<Badge {size} color={config.color}>
    <IconComponent class="w-3 h-3" />
    {config.label}
</Badge>
