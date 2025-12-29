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

    const languageOptions = [
        { value: 'en', name: 'English' },
        { value: 'es', name: 'Spanish' },
        { value: 'fr', name: 'French' },
        { value: 'de', name: 'German' },
        { value: 'pt', name: 'Portuguese' }
    ];

    const timezoneOptions = [
        { value: 'UTC', name: 'UTC' },
        { value: 'America/New_York', name: 'Eastern Time (US & Canada)' },
        { value: 'Europe/London', name: 'London' },
        { value: 'Europe/Paris', name: 'Paris' },
        { value: 'Asia/Tokyo', name: 'Tokyo' }
    ];

    onMount(async () => {
        try {
            const res = await fetch('/api/settings');
            if (res.ok) {
                const data = await res.json();
                // Ensure default values for new settings
                settings = {
                    sidebar_enabled: 'internal',
                    site_name: 'CerneCMS',
                    site_description: '',
                    site_logo: '',
                    site_favicon: '',
                    site_language: 'en',
                    site_timezone: 'UTC',
                    site_currency: 'USD',
                    maintenance_mode: false,
                    maintenance_message: 'The site is currently under maintenance. Please check back later.',
                    ...data
                };
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

<div class="max-w-3xl mx-auto space-y-6 pb-12">
    <!-- General Settings -->
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <div class="mb-8 border-b pb-4">
            <Heading tag="h2" class="text-xl font-bold">General Settings</Heading>
            <p class="text-gray-500 text-sm mt-1">Basic information about your website.</p>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <Label for="site-name" class="font-semibold">Site Name</Label>
                    <input id="site-name" type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" bind:value={settings.site_name} />
                </div>
                <div class="space-y-2">
                    <Label for="site-currency" class="font-semibold">Site Currency</Label>
                    <input id="site-currency" type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" bind:value={settings.site_currency} placeholder="e.g. USD, EUR, GBP" />
                </div>
            </div>

            <div class="space-y-2">
                <Label for="site-description" class="font-semibold">Site Description (SEO)</Label>
                <textarea id="site-description" rows="3" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" bind:value={settings.site_description}></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <Label for="site-logo" class="font-semibold">Site Logo URL</Label>
                    <input id="site-logo" type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" bind:value={settings.site_logo} placeholder="/uploads/logo.png" />
                </div>
                <div class="space-y-2">
                    <Label for="site-favicon" class="font-semibold">Site Favicon URL</Label>
                    <input id="site-favicon" type="text" class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500" bind:value={settings.site_favicon} placeholder="/favicon.ico" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <Label for="site-language" class="font-semibold">Site Language</Label>
                    <Select id="site-language" items={languageOptions} bind:value={settings.site_language} />
                </div>
                <div class="space-y-2">
                    <Label for="site-timezone" class="font-semibold">Site Timezone</Label>
                    <Select id="site-timezone" items={timezoneOptions} bind:value={settings.site_timezone} />
                </div>
            </div>
        </div>
    </div>

    <!-- Interface Settings -->
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <div class="mb-8 border-b pb-4">
            <Heading tag="h2" class="text-xl font-bold">Interface & Layout</Heading>
            <p class="text-gray-500 text-sm mt-1">Control how the frontend looks and behaves.</p>
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
        </div>
    </div>

    <!-- Maintenance Mode -->
    <div class="bg-white p-8 rounded-xl shadow-sm border border-red-100">
        <div class="mb-8 border-b pb-4 flex items-center justify-between">
            <div>
                <Heading tag="h2" class="text-xl font-bold text-red-600">Maintenance Mode</Heading>
                <p class="text-gray-500 text-sm mt-1">Take the site offline for all users except administrators.</p>
            </div>
            <Toggle color="red" bind:checked={settings.maintenance_mode} />
        </div>

        {#if settings.maintenance_mode}
            <div class="space-y-2">
                <Label for="maintenance-message" class="font-semibold text-red-700">Maintenance Message</Label>
                <textarea id="maintenance-message" rows="3" class="w-full rounded-lg border-red-200 focus:border-red-500 focus:ring-red-500 bg-red-50" bind:value={settings.maintenance_message}></textarea>
            </div>
        {/if}
    </div>

    <!-- Save Bar -->
    <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100 sticky bottom-4 flex items-center justify-between z-10">
        <div class="flex items-center gap-3">
            {#if saved}
                <div class="flex items-center gap-2 text-green-600 font-medium animate-bounce">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Settings saved successfully!
                </div>
            {:else}
                <span class="text-gray-400 text-sm">You have unsaved changes.</span>
            {/if}
        </div>
        <Button size="lg" onclick={save} disabled={loading} class="px-12">
            {loading ? 'Saving...' : 'Save Settings'}
        </Button>
    </div>
</div>
