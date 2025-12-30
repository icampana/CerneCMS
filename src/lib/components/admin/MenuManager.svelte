<script>
    import { onMount, tick } from 'svelte';
    import { Button, Table, TableBody, TableBodyCell, TableBodyRow, TableHead, TableHeadCell, Modal, Label, Input, Checkbox } from 'flowbite-svelte';
    import { EditSolid, TrashBinSolid, PlusOutline, AngleRightOutline, AngleLeftOutline, AngleUpOutline, AngleDownOutline, ListMusicSolid } from 'flowbite-svelte-icons';
    import MenuEditor from './MenuEditor.svelte';


    // State
    let menus = $state([]);
    let activeMenuId = $state(null);
    let isCreateModalOpen = $state(false);
    let isEditModalOpen = $state(false);

    // Form State
    let formName = $state('');
    let formSlug = $state('');
    let formIsPrimary = $state(false);
    let editingId = $state(null);

    onMount(() => {
        fetchMenus();
    });

    async function fetchMenus() {
        try {
            const res = await fetch('/api/menus');
            menus = await res.json();
        } catch (e) {
            console.error(e);
        }
    }

    function openCreate() {
        formName = '';
        formSlug = '';
        formIsPrimary = false;
        editingId = null;
        isCreateModalOpen = true;
    }

    function openEdit(menu) {
        formName = menu.name;
        formSlug = menu.slug;
        formIsPrimary = !!menu.is_primary;
        editingId = menu.id;
        isEditModalOpen = true;
    }

    function generateSlug(name) {
        return name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
    }

    async function handleSave() {
        const payload = {
            name: formName,
            slug: formSlug || generateSlug(formName),
            is_primary: formIsPrimary
        };

        const url = editingId ? `/api/menus/${editingId}` : '/api/menus';
        const method = editingId ? 'PUT' : 'POST';

        try {
            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                await fetchMenus();
                isCreateModalOpen = false;
                isEditModalOpen = false;
            } else {
                alert('Error saving menu');
            }
        } catch (e) {
            console.error(e);
        }
    }

    async function handleDelete(id) {
        if (!confirm('Are you sure you want to delete this menu?')) return;
        try {
            await fetch(`/api/menus/${id}`, { method: 'DELETE' });
            await fetchMenus();
            if (activeMenuId === id) activeMenuId = null;
        } catch (e) {
            alert('Error deleting menu');
        }
    }

    function manageItems(id) {
        activeMenuId = id;
    }
</script>

{#if activeMenuId}
    <div class="mb-4">
        <Button color="light" onclick={() => activeMenuId = null}>← Back to Menus</Button>
    </div>
    {#key activeMenuId}
        <MenuEditor menuId={activeMenuId} />
    {/key}
{:else}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold">Menus</h2>
        <Button onclick={openCreate}>
            <PlusOutline class="w-4 h-4 mr-2" />
            New Menu
        </Button>
    </div>

    <div class="border rounded-lg overflow-hidden">
        <Table>
            <TableHead>
                <TableHeadCell>Name</TableHeadCell>
                <TableHeadCell>Slug</TableHeadCell>
                <TableHeadCell>Primary</TableHeadCell>
                <TableHeadCell>Actions</TableHeadCell>
            </TableHead>
            <TableBody>
                {#each menus as menu}
                    <TableBodyRow>
                        <TableBodyCell>{menu.name}</TableBodyCell>
                        <TableBodyCell>{menu.slug}</TableBodyCell>
                        <TableBodyCell>
                            {#if menu.is_primary}
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Primary</span>
                            {/if}
                        </TableBodyCell>
                        <TableBodyCell>
                            <div class="flex gap-2">
                                <Button size="xs" onclick={() => manageItems(menu.id)}>Manage Items</Button>
                                <Button size="xs" color="light" onclick={() => openEdit(menu)}>
                                    <EditSolid class="w-4 h-4" />
                                </Button>
                                <Button size="xs" color="red" onclick={() => handleDelete(menu.id)}>
                                    <TrashBinSolid class="w-4 h-4" />
                                </Button>
                            </div>
                        </TableBodyCell>
                    </TableBodyRow>
                {/each}
                {#if menus.length === 0}
                    <TableBodyRow>
                        <TableBodyCell colspan="4" class="text-center py-4 text-gray-500">
                            No menus found. Create one to get started.
                        </TableBodyCell>
                    </TableBodyRow>
                {/if}
            </TableBody>
        </Table>
    </div>


{/if}

<!-- Create/Edit Modal -->
<Modal bind:open={isCreateModalOpen} size="sm" autoclose={false} title="Create Menu">
    <div class="flex flex-col space-y-4">
        <div>
            <Label class="mb-2">Name</Label>
            <Input bind:value={formName} placeholder="Main Menu" oninput={() => { if(!editingId) formSlug = generateSlug(formName); }} />
        </div>
        <div>
            <Label class="mb-2">Slug</Label>
            <Input bind:value={formSlug} placeholder="main-menu" />
        </div>
        <Checkbox bind:checked={formIsPrimary}>Set as Primary Menu</Checkbox>
        <div class="flex justify-end gap-2">
            <Button color="alternative" onclick={() => isCreateModalOpen = false}>Cancel</Button>
            <Button onclick={handleSave}>Create</Button>
        </div>
    </div>
</Modal>

<Modal bind:open={isEditModalOpen} size="sm" autoclose={false} title="Edit Menu">
    <div class="flex flex-col space-y-4">
        <div>
            <Label class="mb-2">Name</Label>
            <Input bind:value={formName} />
        </div>
        <div>
            <Label class="mb-2">Slug</Label>
            <Input bind:value={formSlug} />
        </div>
        <Checkbox bind:checked={formIsPrimary}>Set as Primary Menu</Checkbox>
        <div class="flex justify-end gap-2">
            <Button color="alternative" onclick={() => isEditModalOpen = false}>Cancel</Button>
            <Button onclick={handleSave}>Save</Button>
        </div>
    </div>
</Modal>
