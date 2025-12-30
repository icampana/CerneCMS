<script>
  import { Modal, Label, Input, Button, Radio } from 'flowbite-svelte';
  import InternalLinkPicker from '../ui/InternalLinkPicker.svelte';
  import { editorStore } from '../../stores/editor.svelte.js';

  let open = $derived(editorStore.linkModalOpen);
  let linkType = $state('internal'); // internal, external
  let linkValue = $state('');
  let pageSlug = $state('');

  function close() {
      editorStore.closeLinkModal();
      linkType = 'internal';
      linkValue = '';
      pageSlug = '';
  }

  function handleInternalSelect(page) {
      pageSlug = `/${page.slug}`;
      // Maybe also title if needed?
  }

  function save() {
      let finalUrl = '';
      if (linkType === 'internal') {
          finalUrl = pageSlug;
      } else {
          finalUrl = linkValue;
      }

      if (finalUrl) {
          editorStore.confirmLink(finalUrl);
      }
      close();
  }

  function removeLink() {
      editorStore.removeLink();
      close();
  }
</script>

<Modal title="Insert Link" bind:open={editorStore.linkModalOpen} size="sm" autoclose={false} on:close={close}>
    <div class="flex flex-col space-y-4">
        <div>
            <Label class="mb-2">Link Type</Label>
            <div class="flex gap-4">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" bind:group={linkType} value="internal" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-900">Internal Page</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="radio" bind:group={linkType} value="external" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-900">External URL</span>
                </label>
            </div>
        </div>

        {#if linkType === 'internal'}
            <div>
                <Label class="mb-2">Search Page</Label>
                <InternalLinkPicker onselect={handleInternalSelect} />
                {#if pageSlug}
                    <p class="text-xs text-green-600 mt-1">Linking to: {pageSlug}</p>
                {/if}
            </div>
        {:else}
            <div>
                <Label class="mb-2">URL</Label>
                <Input type="url" bind:value={linkValue} placeholder="https://example.com" />
            </div>
        {/if}

        <div class="flex justify-between mt-4">
            <Button color="red" size="sm" onclick={removeLink} outline>Remove Link</Button>
            <div class="flex gap-2">
                <Button color="alternative" onclick={close}>Cancel</Button>
                <Button onclick={save}>Insert</Button>
            </div>
        </div>
    </div>
</Modal>
