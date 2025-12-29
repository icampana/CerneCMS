<script>
    import { onMount } from 'svelte';
    import { Button, Label, Select, Toggle, Heading } from 'flowbite-svelte';

    let settings = $state({
        sidebar_enabled: 'internal' // all, internal, none
    });

    let loading = $state(false);
    let saved = $state(false);

    const sidebarOptions = [
        { value: 'all', name: 'Enabled on All Pages' },
        { value: 'internal', name: 'Enabled on Internal Pages (Hidden on Home)' },
        { value: 'none', name: 'Disabled Globally' }
    ];

    onMount(async () => {
        try {
            const res = await fetch('/api/settings');
            if (res.ok) {
                const data = await res.json();
                settings = { ...settings, ...data };
            }
        } catch (e) {
            console.error(e);
        }
    });

    async function save() {
        loading = true;
        saved = false;
        try {
            const res = await fetch('/api/settings', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(settings)
            });

            if (res.ok) {
                saved = true;
                setTimeout(() => saved = false, 3000);
            }
        } catch (e) {
            alert('Error saving settings');
        } finally {
            loading = false;
        }
    }
</script>

<div class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
    <div class="mb-8 border-b pb-4">
        <Heading tag="h2" class="text-xl font-bold">Site Settings</Heading>
        <p class="text-gray-500 text-sm mt-1">Configure global application behavior.</p>
    </div>

    <div class="space-y-6">
        <div>
            <Label for="sidebar-mode" class="mb-2 text-base font-semibold">Sidebar Visibility</Label>
            <p class="text-xs text-gray-500 mb-3">
                Controls where the sidebar (containing local navigation) is displayed.
                You can override this on a per-page basis in the page editor.
            </p>
            <Select id="sidebar-mode" items={sidebarOptions} bind:value={settings.sidebar_enabled} />
        </div>

        <div class="pt-4 border-t flex items-center justify-between">
            {#if saved}
                <span class="text-green-600 font-medium text-sm">Settings saved successfully!</span>
            {:else}
                <span></span>
            {/if}
            <Button onclick={save} disabled={loading}>
                {loading ? 'Saving...' : 'Save Settings'}
            </Button>
        </div>
    </div>
</div>
