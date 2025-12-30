<script>
    import { Modal, Label, Input, Button, Select, Toggle, Helper } from 'flowbite-svelte';
    import { TrashBinSolid, PlusOutline, ExclamationCircleSolid } from 'flowbite-svelte-icons';
    import { editorStore } from '../../../stores/editor.svelte.js';

    let { open = $bindable(), onClose, onSave, editFormId = null } = $props();

    let name = $state('');
    let slug = $state('');
    let fields = $state([]);
    let settings = $state({
        submitLabel: 'Submit',
        successMessage: 'Thank you for your submission.',
        notificationEmail: ''
    });
    let isLoading = $state(false);
    let error = $state(null);

    const fieldTypes = [
        { value: 'text', name: 'Text Input' },
        { value: 'email', name: 'Email Address' },
        { value: 'textarea', name: 'Text Area' },
        { value: 'number', name: 'Number' },
        { value: 'select', name: 'Dropdown' },
        { value: 'checkbox', name: 'Checkbox' },
        { value: 'date', name: 'Date' }
    ];

    // Initialize or Fetch if editing
    $effect(() => {
        if (open && editFormId) {
             fetchForm(editFormId);
        } else if (open) {
            reset();
        }
    });

    async function fetchForm(id) {
        isLoading = true;
        try {
            const res = await fetch(`/api/forms/${id}`);
            if (!res.ok) throw new Error('Failed to load form');
            const data = await res.json();
            name = data.name;
            slug = data.slug;
            fields = Array.isArray(data.fields_json) ? data.fields_json : JSON.parse(data.fields_json || '[]');
            settings = {
                ...settings,
                ...(Array.isArray(data.settings_json) ? data.settings_json : JSON.parse(data.settings_json || '{}'))
            };
        } catch (e) {
            error = e.message;
        } finally {
            isLoading = false;
        }
    }

    function reset() {
        name = '';
        slug = '';
        fields = [];
        settings = { submitLabel: 'Submit', successMessage: 'Thank you for your submission.', notificationEmail: '' };
        error = null;
    }

    function addField() {
        fields = [...fields, {
            id: crypto.randomUUID(),
            type: 'text',
            name: 'field_' + (fields.length + 1),
            label: 'New Field',
            placeholder: '',
            required: false,
            options: '', // Comma separated for select
            width: 'full' // full or half
        }];
    }

    function removeField(index) {
        fields = fields.filter((_, i) => i !== index);
    }

    function updateField(index, key, value) {
        const newFields = [...fields];
        newFields[index][key] = value;
        fields = newFields;
    }

    async function save() {
        if (!name) {
            error = 'Form name is required';
            return;
        }

        isLoading = true;
        error = null;

        // Process fields options
        const processedFields = fields.map(f => {
            if (f.type === 'select' && typeof f.options === 'string') {
                return { ...f, options: f.options.split(',').map(s => s.trim()) };
            }
            return f;
        });

        const payload = {
            name,
            slug: slug || undefined, // Let backend generate if empty
            fields_json: JSON.stringify(processedFields),
            settings_json: JSON.stringify(settings),
            status: 'active'
        };

        try {
            const url = editFormId ? `/api/forms/${editFormId}` : '/api/forms';
            const method = editFormId ? 'PUT' : 'POST';

            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (!res.ok) {
                const data = await res.json();
                throw new Error(data.error || 'Failed to save form');
            }

            const savedForm = await res.json();
            onSave(savedForm);
            onClose();
        } catch (e) {
            error = e.message;
        } finally {
            isLoading = false;
        }
    }
</script>

<Modal title={editFormId ? 'Edit Form' : 'Create New Form'} bind:open={open} size="xl" autoclose={false} class="w-full">
    <div class="space-y-6 max-h-[70vh] overflow-y-auto px-1">
        {#if error}
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                <span class="font-medium">Error!</span> {error}
            </div>
        {/if}

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <Label class="mb-2">Form Name</Label>
                <Input bind:value={name} placeholder="e.g. Contact Us" required />
            </div>
            <div>
                 <Label class="mb-2">Slug (Optional)</Label>
                 <Input bind:value={slug} placeholder="contact-us" />
            </div>
        </div>

        <hr class="border-gray-200" />

        <div class="flex justify-between items-center">
            <h3 class="text-lg font-medium">Form Fields</h3>
            <Button size="xs" color="light" onclick={addField}>
                <PlusOutline class="w-4 h-4 mr-1" /> Add Field
            </Button>
        </div>

        <div class="space-y-4">
            {#each fields as field, index (field.id)}
                <div class="p-4 border border-gray-200 rounded-lg bg-gray-50 relative group">
                    <button
                        class="absolute top-2 right-2 text-gray-400 hover:text-red-500"
                        onclick={() => removeField(index)}
                        title="Remove Field"
                    >
                        <TrashBinSolid class="w-4 h-4" />
                    </button>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-2">
                        <div class="md:col-span-4">
                            <Label class="mb-1 text-xs">Label</Label>
                            <Input size="sm" value={field.label} oninput={(e) => updateField(index, 'label', e.target.value)} />
                        </div>
                        <div class="md:col-span-3">
                             <Label class="mb-1 text-xs">Type</Label>
                             <Select size="sm" items={fieldTypes} value={field.type} onchange={(e) => updateField(index, 'type', e.target.value)} />
                        </div>
                        <div class="md:col-span-3">
                             <Label class="mb-1 text-xs">Field Name (API key)</Label>
                             <Input size="sm" value={field.name} oninput={(e) => updateField(index, 'name', e.target.value)} />
                        </div>
                        <div class="md:col-span-2 flex items-end pb-2">
                            <Toggle size="small" checked={field.required} onchange={(e) => updateField(index, 'required', e.target.checked)}>Required</Toggle>
                        </div>
                    </div>

                    {#if field.type === 'select'}
                        <div class="mt-2">
                            <Label class="mb-1 text-xs">Options (comma separated)</Label>
                            <Input size="sm" value={field.options} oninput={(e) => updateField(index, 'options', e.target.value)} placeholder="Option 1, Option 2, Option 3" />
                        </div>
                    {/if}
                     {#if field.type === 'text' || field.type === 'textarea'}
                        <div class="mt-2">
                            <Label class="mb-1 text-xs">Placeholder</Label>
                            <Input size="sm" value={field.placeholder} oninput={(e) => updateField(index, 'placeholder', e.target.value)} />
                        </div>
                    {/if}
                </div>
            {/each}

            {#if fields.length === 0}
                <div class="text-center py-8 text-gray-500 bg-gray-50 border border-dashed border-gray-300 rounded-lg">
                    No fields added yet.
                </div>
            {/if}
        </div>

        <hr class="border-gray-200" />

        <h3 class="text-lg font-medium">Settings</h3>
        <div class="grid grid-cols-1 gap-4">
             <div>
                <Label class="mb-2">Submit Button Text</Label>
                <Input bind:value={settings.submitLabel} />
            </div>
             <div>
                <Label class="mb-2">Success Message</Label>
                <Input bind:value={settings.successMessage} />
            </div>
             <div>
                <Label class="mb-2">Notification Email (Optional)</Label>
                <Input bind:value={settings.notificationEmail} type="email" placeholder="admin@example.com" />
                <Helper>Leave distinct to use site default if configured.</Helper>
            </div>
        </div>

        <hr class="border-gray-200" />

        <div class="w-full flex justify-end gap-2 pt-4">
            <Button color="alternative" onclick={onClose}>Cancel</Button>
            <Button onclick={save} disabled={isLoading}>
                {#if isLoading}
                    Saving...
                {:else}
                    Save Form
                {/if}
            </Button>
        </div>
    </div>
</Modal>
