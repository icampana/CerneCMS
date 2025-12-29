<script>
  import { Modal, Label, Input, Select, Button, Checkbox, Radio } from 'flowbite-svelte';
  import InternalLinkPicker from '../ui/InternalLinkPicker.svelte';

  export let open = false;
  export let item = null; // If null, creating new
  export let onSave = () => {};
  export let onClose = () => {};

  let title = '';
  let linkType = 'internal'; // internal, external, anchor
  let linkValue = ''; // for external/anchor
  let targetPageId = null;
  let openNewTab = false;
  let isInternalPickerOpen = false;

  // Initialize from item
  $: if (open && item) {
    title = item.title;
    linkType = item.link_type;
    linkValue = item.link_value;
    targetPageId = item.target_page_id;
    openNewTab = !!item.open_new_tab;
  } else if (open && !item) {
    // Reset
    title = '';
    linkType = 'internal';
    linkValue = '';
    targetPageId = null;
    openNewTab = false;
  }

  function handleInternalSelect(event) {
    const page = event.detail;
    targetPageId = page.id;
    if (!title) {
        title = page.title;
    }
  }

  function save() {
    onSave({
      ...item,
      title,
      link_type: linkType,
      link_value: linkValue,
      target_page_id: targetPageId,
      open_new_tab: openNewTab
    });
    onClose();
  }
</script>

<Modal bind:open={open} size="md" autoclose={false} class="w-full" on:close={onClose} title={item ? 'Edit Menu Item' : 'Add Menu Item'}>
  <div class="flex flex-col space-y-4">

    <div>
      <Label class="mb-2">Link Type</Label>
      <div class="flex gap-4">
        <Radio bind:group={linkType} value="internal">Internal Page</Radio>
        <Radio bind:group={linkType} value="external">External URL</Radio>
        <Radio bind:group={linkType} value="anchor">Anchor</Radio>
      </div>
    </div>

    {#if linkType === 'internal'}
      <div>
        <Label class="mb-2">Select Page</Label>
        <InternalLinkPicker
            selectedPageId={targetPageId}
            on:select={handleInternalSelect}
        />
        {#if targetPageId}
            <div class="mt-1 text-xs text-green-600 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Page selected
            </div>
        {/if}
      </div>
    {:else if linkType === 'external'}
      <div>
        <Label class="mb-2">URL</Label>
        <Input type="url" bind:value={linkValue} placeholder="https://example.com" />
      </div>
    {:else}
      <div>
        <Label class="mb-2">Anchor (#)</Label>
        <div class="flex gap-2">
            <div class="flex-shrink-0 flex items-center px-3 bg-gray-100 border border-gray-300 rounded-l-lg">
                #
            </div>
            <Input type="text" class="rounded-l-none" bind:value={linkValue} placeholder="section-id" />
        </div>
      </div>
    {/if}

    <div>
      <Label class="mb-2">Label</Label>
      <Input type="text" bind:value={title} placeholder="Menu Link Text" required />
    </div>

    <div class="flex items-center gap-2">
      <Checkbox bind:checked={openNewTab}>Open in new tab</Checkbox>
    </div>

    <div class="flex justify-end gap-2 mt-4">
        <Button color="alternative" on:click={onClose}>Cancel</Button>
        <Button on:click={save}>Save</Button>
    </div>

  </div>
</Modal>
