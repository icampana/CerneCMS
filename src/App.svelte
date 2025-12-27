<script>
    import Editor from './lib/components/Editor.svelte';
</script>

<div class="cms-shell p-8 max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-2">Editor</h1>
        <p class="text-gray-600">Building the best CMS experience with Svelte 5 and Tiptap.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <Editor />
    </div>
</div>
