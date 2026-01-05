<script>
    import { onMount } from 'svelte';
    import { Button, Spinner } from 'flowbite-svelte';
    import EditSolid from 'flowbite-svelte-icons/EditSolid.svelte';
    import TrashBinSolid from 'flowbite-svelte-icons/TrashBinSolid.svelte';
    import PlusOutline from 'flowbite-svelte-icons/PlusOutline.svelte';
    import AngleRightOutline from 'flowbite-svelte-icons/AngleRightOutline.svelte';
    import AngleLeftOutline from 'flowbite-svelte-icons/AngleLeftOutline.svelte';
    import AngleUpOutline from 'flowbite-svelte-icons/AngleUpOutline.svelte';
    import AngleDownOutline from 'flowbite-svelte-icons/AngleDownOutline.svelte';
    import MenuItemModal from './MenuItemModal.svelte';

    let { menuId } = $props();

    let items = $state([]);
    let menu = $state(null);
    let loading = $state(true);
    let isModalOpen = $state(false);
    let editingItem = $state(null); // null = create new

    onMount(() => {
        fetchMenu();
    });

    async function fetchMenu() {
        loading = true;
        try {
            const res = await fetch(`/api/menus/${menuId}`);
            if (res.ok) {
                menu = await res.json();
                // Flatten the tree for easy editing
                items = flattenItems(menu.items);
            }
        } catch (e) {
            console.error(e);
        } finally {
            loading = false;
        }
    }

    function flattenItems(tree, depth = 0) {
        let flat = [];
        if (!tree) return flat;

        tree.forEach(item => {
            flat.push({ ...item, depth }); // Add visual depth property
            if (item.children && item.children.length) {
                flat = flat.concat(flattenItems(item.children, depth + 1));
            }
        });
        return flat;
    }

    function openAdd() {
        editingItem = null;
        isModalOpen = true;
    }

    function openEdit(item) {
        editingItem = item;
        isModalOpen = true;
    }

    async function handleSaveItem(itemData) {
        // If create
        if (!itemData.id) {
            // New item appends to end, depth 0 by default for now
            const payload = {
                title: itemData.title,
                link_type: itemData.link_type,
                link_value: itemData.link_value,
                target_page_id: itemData.target_page_id,
                open_new_tab: itemData.open_new_tab,
                parent_id: null
            };

            await fetch(`/api/menus/${menuId}/items`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
        } else {
            // Update
            const payload = {
                title: itemData.title,
                link_type: itemData.link_type,
                link_value: itemData.link_value,
                target_page_id: itemData.target_page_id,
                open_new_tab: itemData.open_new_tab
                // Don't update parent_id here, handled by reorder
            };

            await fetch(`/api/menu-items/${itemData.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
        }

        await fetchMenu(); // Reload to refresh list and tree
    }

    async function handleDelete(id) {
        if (!confirm('Delete this item? Children will also be deleted.')) return;
        await fetch(`/api/menu-items/${id}`, { method: 'DELETE' });
        await fetchMenu();
    }

    // --- Reordering Logic ---

    // Move in array
    function arrayMove(arr, fromIndex, toIndex) {
        const element = arr[fromIndex];
        arr.splice(fromIndex, 1);
        arr.splice(toIndex, 0, element);
    }

    async function saveOrder() {
        // Reconstruct parent_ids based on depth
        const updates = [];
        let currentParentMap = { 0: null, 1: null }; // Tracks last item at depth 0

        for (let i = 0; i < items.length; i++) {
            const item = items[i];
            let parentId = null;

            if (item.depth === 1) {
                // Determine parent: the last item with depth 0 seen before this
                parentId = currentParentMap[0];
                if (!parentId) {
                    // Invalid state: depth 1 item first or after nothing? Force depth 0
                    item.depth = 0;
                    parentId = null;
                }
            }

            // Update map
            if (item.depth === 0) {
                currentParentMap[0] = item.id;
            }

            updates.push({
                id: item.id,
                parent_id: parentId,
                sort_order: i
            });
        }

        try {
            const res = await fetch('/api/menu-items/reorder', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(updates)
            });
            if (!res.ok) alert('Error saving order');
            // Fetch clean state from server
            await fetchMenu();
        } catch (e) {
            console.error(e);
        }
    }

    function moveUp(index) {
        if (index === 0) return;
        // Swap visual items
        const newItems = [...items];
        // Logic: Should we respect depth?
        // Just move effectively in the flat list.
        // Then user clicks "Save" to persist logic.
        // It's simpler to move in flat list.
        arrayMove(newItems, index, index - 1);
        items = newItems;
        saveOrder(); // Auto-save for better UX? Or manual button? Let's auto-save for simplicity of user mental model
    }

    function moveDown(index) {
        if (index === items.length - 1) return;
        const newItems = [...items];
        arrayMove(newItems, index, index + 1);
        items = newItems;
        saveOrder();
    }

    function indent(index) {
        if (index === 0) return; // First item must be root
        const newItems = [...items];
        const item = newItems[index];
        const prev = newItems[index - 1];

        // Can only indent if previous is level 0 (so max depth 1)
        // If prev is 0, we can go 1.
        // If prev is 1, we can't go 2 (max 2 levels).
        // Actually, if prev is 1, and we become 1, we are sibling of prev.
        // Wait, "Indent" means "Make child of Previous".
        // If prev is depth 0, we can become depth 1.
        // If prev is depth 1, we can become depth 2 (if supported). User said max 2 levels (0 and 1?).
        // "Nesting, no more than 2 levels" -> Root (0) -> Child (1).

        if (prev.depth === 0 && item.depth === 0) {
            item.depth = 1;
            items = newItems;
            saveOrder();
        }
    }

    function outdent(index) {
        const newItems = [...items];
        const item = newItems[index];

        if (item.depth > 0) {
            item.depth = 0;
            items = newItems;
            saveOrder();
        }
    }

</script>

{#if loading}
    <div class="p-8 text-center"><Spinner /></div>
{:else}
    <div class="bg-white p-6 rounded-lg shadow-sm">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-bold">Edit Menu: {menu.name}</h3>
            <Button onclick={openAdd}>
                <PlusOutline class="w-4 h-4 mr-2" />
                Add Item
            </Button>
        </div>

        <div class="space-y-2">
            {#each items as item, i (item.id)}
                <div class="flex items-center gap-2 p-3 border rounded-lg bg-white hover:bg-gray-50 transition-colors"
                     style="margin-left: {item.depth * 32}px">

                    <!-- Drag Handle / Move Controls -->
                    <div class="flex flex-col gap-1 mr-2 text-gray-400">
                        <button onclick={() => moveUp(i)} disabled={i === 0} class="hover:text-blue-600 disabled:opacity-30"><AngleUpOutline class="w-3 h-3"/></button>
                        <button onclick={() => moveDown(i)} disabled={i === items.length - 1} class="hover:text-blue-600 disabled:opacity-30"><AngleDownOutline class="w-3 h-3"/></button>
                    </div>

                    <div class="flex-1">
                        <div class="font-medium text-gray-900">{item.title}</div>
                        <div class="text-xs text-gray-400 flex gap-2">
                            <span class="uppercase tracking-wider text-[10px] bg-gray-100 px-1 rounded">{item.link_type}</span>
                            <span>
                                {#if item.link_type === 'internal' && item.target_page_id}
                                    Page ID: {item.target_page_id}
                                {:else}
                                    {item.link_value}
                                {/if}
                            </span>
                        </div>
                    </div>

                    <!-- Indentation Controls -->
                    <div class="flex gap-1 border-r pr-2 mr-2 border-gray-200">
                        <button onclick={() => outdent(i)} disabled={item.depth === 0} class="p-1 hover:bg-gray-200 rounded disabled:opacity-30" title="Outdent">
                             <AngleLeftOutline class="w-4 h-4 text-gray-500" />
                        </button>
                        <button onclick={() => indent(i)}
                                disabled={item.depth >= 1 || i === 0 || items[i-1].depth >= 1}
                                class="p-1 hover:bg-gray-200 rounded disabled:opacity-30" title="Indent">
                             <AngleRightOutline class="w-4 h-4 text-gray-500" />
                        </button>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-1">
                        <button onclick={() => openEdit(item)} class="p-2 hover:bg-blue-100 text-blue-600 rounded">
                            <EditSolid class="w-4 h-4" />
                        </button>
                        <button onclick={() => handleDelete(item.id)} class="p-2 hover:bg-red-100 text-red-600 rounded">
                            <TrashBinSolid class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            {/each}

            {#if items.length === 0}
                <div class="text-center py-8 text-gray-400 border-2 border-dashed rounded-lg">
                    No items in this menu yet.
                </div>
            {/if}
        </div>
    </div>
{/if}

<MenuItemModal
    bind:open={isModalOpen}
    item={editingItem}
    onSave={handleSaveItem}
    onClose={() => isModalOpen = false}
/>
