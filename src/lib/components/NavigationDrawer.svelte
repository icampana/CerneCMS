<script>
    import { Drawer, Sidebar, SidebarBrand, SidebarGroup, SidebarItem, SidebarWrapper, CloseButton } from 'flowbite-svelte';
    import { sineIn } from 'svelte/easing';
    import { editorStore } from '../stores/editor.svelte.js';
    import { location } from 'svelte-spa-router';
    import {
        GridSolid,
        FileLinesSolid,
        ImageSolid,
        CogSolid,
        ListMusicSolid
    } from 'flowbite-svelte-icons';

    let transitionParams = {
        x: -320,
        duration: 200,
        easing: sineIn
    };

    const site = {
      name: "Cerne CMS",
      href: "/admin",
      img: "/images/cerne-logo.svg"
    };

    // Pattern matching BlockSettingsDrawer.svelte
    // Use local state + effect sync
    let isOpen = $state(false);

    $effect(() => {
        isOpen = editorStore.navDrawerOpen;
    });

    function handleClose() {
        editorStore.closeNavDrawer();
    }

    function handleKeydown(e) {
        if (e.key === 'Escape' && isOpen) {
            handleClose();
        }
    }
</script>

<svelte:window onkeydown={handleKeydown} />

<!-- Note: dismissable={true} is default, but we might want {false} if click-outside is buggy.
     Let's try {true} first with the stopPropagation fix in App.svelte.
     Actually, let's stick to the user's advice: match BlockSettingsDrawer which has dismissable={false}.
     We can re-enable backdrop click behavior later if needed, or implement manual backdrop.
-->
<Drawer
    transitionType="fly"
    {transitionParams}
    open={isOpen}
    dismissable={false}
    id="nav-drawer"
    width="w-72"
    class="bg-gray-50 dark:bg-gray-900"
>
    <!-- Header with Logo and Close -->
    <div class="flex items-center justify-between p-4 mb-2 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                <span class="text-white font-bold text-lg">C</span>
            </div>
            <div>
                <h5 class="text-lg font-bold text-gray-800 dark:text-white">CerneCMS</h5>
                <p class="text-xs text-gray-500 dark:text-gray-400">Admin Panel</p>
            </div>
        </div>
        <CloseButton onclick={handleClose} class="text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg" />
    </div>

    <!-- Navigation -->
    <div class="px-2">
        <Sidebar class="w-full">
            <SidebarWrapper class="bg-transparent">
                <SidebarBrand {site} classes={{ img: "h-6 w-6" }} />
                <!-- Main Navigation -->
                <SidebarGroup>
                    <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">Content</p>

                    <SidebarItem label="Dashboard" href="#/" active={$location === '/'} class="rounded-lg mb-1">
                        {#snippet icon()}
                            <GridSolid class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                        {/snippet}
                    </SidebarItem>
                    <SidebarItem label="Pages" href="#/pages" active={$location === '/pages' || $location.includes('/editor')} class="rounded-lg mb-1">
                        {#snippet icon()}
                            <FileLinesSolid class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                        {/snippet}
                    </SidebarItem>
                    <SidebarItem label="Menus" href="#/menus" active={$location.includes('/menus')} class="rounded-lg mb-1">
                        {#snippet icon()}
                            <ListMusicSolid class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                        {/snippet}
                    </SidebarItem>
                    <SidebarItem label="Media" href="#" class="rounded-lg mb-1 opacity-50 cursor-not-allowed">
                        {#snippet icon()}
                            <ImageSolid class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                        {/snippet}
                        {#snippet subtext()}
                            <span class="text-xs bg-gray-200 dark:bg-gray-700 px-2 py-0.5 rounded-full">Soon</span>
                        {/snippet}
                    </SidebarItem>
                </SidebarGroup>

                <!-- System -->
                <SidebarGroup border class="pt-4 mt-4">
                    <p class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">System</p>
                    <SidebarItem label="Settings" href="#/settings" active={$location.includes('/settings')} class="rounded-lg mb-1">
                        {#snippet icon()}
                            <CogSolid class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" />
                        {/snippet}
                    </SidebarItem>
                </SidebarGroup>
            </SidebarWrapper>
        </Sidebar>
    </div>

    <!-- Footer -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800">
        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
            v1.0.0 • Made with ❤️
        </p>
    </div>
</Drawer>
