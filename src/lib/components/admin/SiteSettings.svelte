<script>
    import { onMount } from 'svelte';
    import { Button, Label, Select, Toggle, Heading, Sidebar, SidebarGroup, SidebarItem, SidebarWrapper } from 'flowbite-svelte';
    import { CogSolid, GlobeSolid, ImageSolid, DesktopPcSolid, LockSolid, TrashBinSolid } from 'flowbite-svelte-icons';

    let settings = $state({
        sidebar_enabled: 'internal' // all, internal, none
    });

    let loading = $state(false);
    let saved = $state(false);
    let activeTab = $state('general');

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

    async function clearCache() {
        if (!confirm('Are you sure you want to clear the server cache? This may temporarily slow down the first page load for visitors.')) return;

        try {
            const res = await fetch('/api/settings/cache-clear', { method: 'POST' });
            if (res.ok) {
                alert('Cache cleared successfully!');
            } else {
                alert('Failed to clear cache.');
            }
        } catch (e) {
            console.error(e);
            alert('Error clearing cache.');
        }
    }
</script>

<div class="max-w-6xl mx-auto pb-12">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar Navigation -->
        <div class="lg:col-span-1">
            <Sidebar class="w-full" position="static">
                <SidebarWrapper class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
                    <SidebarGroup>
                        <SidebarItem label="General" onclick={() => activeTab = 'general'} active={activeTab === 'general'}>
                            {#snippet icon()}
                                <CogSolid class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" />
                            {/snippet}
                        </SidebarItem>
                        <SidebarItem label="Localization" onclick={() => activeTab = 'localization'} active={activeTab === 'localization'}>
                            {#snippet icon()}
                                <GlobeSolid class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" />
                            {/snippet}
                        </SidebarItem>
                        <SidebarItem label="Branding" onclick={() => activeTab = 'branding'} active={activeTab === 'branding'}>
                            {#snippet icon()}
                                <ImageSolid class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" />
                            {/snippet}
                        </SidebarItem>
                        <SidebarItem label="Interface" onclick={() => activeTab = 'interface'} active={activeTab === 'interface'}>
                            {#snippet icon()}
                                <DesktopPcSolid class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" />
                            {/snippet}
                        </SidebarItem>
                        <SidebarItem label="Maintenance" onclick={() => activeTab = 'maintenance'} active={activeTab === 'maintenance'}>
                            {#snippet icon()}
                                <LockSolid class="w-5 h-5 text-gray-500 transition duration-75 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-white" />
                            {/snippet}
                        </SidebarItem>
                    </SidebarGroup>
                </SidebarWrapper>
            </Sidebar>
        </div>

        <!-- Content Area -->
        <div class="lg:col-span-3 space-y-6">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 min-h-[500px] relative">

                {#if activeTab === 'general'}
                    <div class="mb-8 border-b dark:border-gray-700 pb-4">
                        <Heading tag="h2" class="text-xl font-bold dark:text-white">General Settings</Heading>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Basic information about your website.</p>
                    </div>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <Label for="site-name" class="font-semibold dark:text-white">Site Name</Label>
                            <input id="site-name" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500" bind:value={settings.site_name} />
                        </div>
                        <div class="space-y-2">
                            <Label for="site-description" class="font-semibold dark:text-white">Site Description (SEO)</Label>
                            <textarea id="site-description" rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500" bind:value={settings.site_description}></textarea>
                        </div>
                    </div>
                {/if}

                {#if activeTab === 'localization'}
                    <div class="mb-8 border-b dark:border-gray-700 pb-4">
                        <Heading tag="h2" class="text-xl font-bold dark:text-white">Localization</Heading>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Manage language, time, and currency settings.</p>
                    </div>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <Label for="site-currency" class="font-semibold dark:text-white">Site Currency</Label>
                            <input id="site-currency" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500" bind:value={settings.site_currency} placeholder="e.g. USD, EUR, GBP" />
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <Label for="site-language" class="font-semibold dark:text-white">Site Language</Label>
                                <Select id="site-language" items={languageOptions} bind:value={settings.site_language} />
                            </div>
                            <div class="space-y-2">
                                <Label for="site-timezone" class="font-semibold dark:text-white">Site Timezone</Label>
                                <Select id="site-timezone" items={timezoneOptions} bind:value={settings.site_timezone} />
                            </div>
                        </div>
                    </div>
                {/if}

                {#if activeTab === 'branding'}
                    <div class="mb-8 border-b dark:border-gray-700 pb-4">
                        <Heading tag="h2" class="text-xl font-bold dark:text-white">Branding</Heading>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Upload and manage site logos and icons.</p>
                    </div>
                    <div class="space-y-6">
                        <div class="space-y-2">
                            <Label for="site-logo" class="font-semibold dark:text-white">Site Logo URL</Label>
                            <input id="site-logo" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500" bind:value={settings.site_logo} placeholder="/uploads/logo.png" />
                        </div>
                        <div class="space-y-2">
                            <Label for="site-favicon" class="font-semibold dark:text-white">Site Favicon URL</Label>
                            <input id="site-favicon" type="text" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-blue-500 focus:ring-blue-500" bind:value={settings.site_favicon} placeholder="/favicon.ico" />
                        </div>
                    </div>
                {/if}

                {#if activeTab === 'interface'}
                    <div class="mb-8 border-b dark:border-gray-700 pb-4">
                        <Heading tag="h2" class="text-xl font-bold dark:text-white">Interface & Layout</Heading>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Control how the frontend looks and behaves.</p>
                    </div>
                    <div class="space-y-6">
                        <div>
                            <Label for="sidebar-mode" class="mb-2 text-base font-semibold dark:text-white">Sidebar Visibility</Label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                                Controls where the sidebar (containing local navigation) is displayed.
                                You can override this on a per-page basis in the page editor.
                            </p>
                            <Select id="sidebar-mode" items={sidebarOptions} bind:value={settings.sidebar_enabled} />
                        </div>
                    </div>
                {/if}

                {#if activeTab === 'maintenance'}
                    <div class="mb-8 border-b dark:border-gray-700 pb-4">
                        <Heading tag="h2" class="text-xl font-bold text-red-600 dark:text-red-400">Maintenance Mode</Heading>
                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Take the site offline for all users except administrators.</p>
                    </div>
                    <div class="space-y-6">
                        <div class="flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                            <div>
                                <span class="font-semibold text-red-800 dark:text-red-300">Enable Maintenance Mode</span>
                                <p class="text-sm text-red-600 dark:text-red-400">When enabled, visitors will see the maintenance message.</p>
                            </div>
                            <Toggle color="red" bind:checked={settings.maintenance_mode} />
                        </div>

                        {#if settings.maintenance_mode}
                            <div class="space-y-2">
                                <Label for="maintenance-message" class="font-semibold text-red-700 dark:text-red-400">Maintenance Message</Label>
                                <textarea id="maintenance-message" rows="3" class="w-full rounded-lg border-red-200 dark:border-red-800 focus:border-red-500 focus:ring-red-500 bg-red-50 dark:bg-red-900/20 dark:text-white" bind:value={settings.maintenance_message}></textarea>
                            </div>
                        {/if}

                        <hr class="border-gray-200 dark:border-gray-700" />

                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div>
                                <span class="font-semibold text-gray-800 dark:text-white">Clear System Cache</span>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Removes all compiled templates. Use this if you don't see your changes on the frontend.</p>
                            </div>
                            <Button color="light" onclick={clearCache} class="!p-2.5">
                                <TrashBinSolid class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                                <span class="ml-2">Clear Cache</span>
                            </Button>
                        </div>
                    </div>
                {/if}

            </div>

            <!-- Save Bar (Sticky inside the column) -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-md border border-gray-100 dark:border-gray-700 flex items-center justify-between sticky bottom-4 z-10">
                <div class="flex items-center gap-3">
                    {#if saved}
                        <div class="flex items-center gap-2 text-green-600 font-medium animate-bounce">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Settings saved successfully!
                        </div>
                    {:else}
                        <span class="text-gray-400 dark:text-gray-500 text-sm">You have unsaved changes.</span>
                    {/if}
                </div>
                <Button size="lg" onclick={save} disabled={loading} class="px-12">
                    {loading ? 'Saving...' : 'Save Settings'}
                </Button>
            </div>
        </div>
    </div>
</div>
