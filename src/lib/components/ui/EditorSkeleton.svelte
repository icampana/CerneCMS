<script>
    import { Skeleton } from 'flowbite-svelte';
</script>

<div class="editor-wrapper border border-gray-200 rounded-lg bg-white shadow-sm p-4 animate-pulse">
    <!-- Header skeleton -->
    <div class="mb-6 flex justify-between items-center bg-white border-b border-gray-100 pb-4">
        <div class="w-2/3">
             <Skeleton class="h-4 w-24 mb-3" />
             <Skeleton class="h-8 w-3/4 rounded-lg" />
        </div>
        <div>
             <Skeleton class="h-8 w-8 rounded-lg" />
        </div>
    </div>

    <!-- Toolbar skeleton -->
    <div class="flex gap-2 mb-6 border-b border-gray-100 pb-4">
        {#each Array(8) as _}
            <Skeleton class="h-8 w-8 rounded" />
        {/each}
        <div class="flex-1"></div>
        <Skeleton class="h-8 w-24 rounded" />
    </div>

    <!-- Content skeleton -->
    <div class="space-y-4 max-w-4xl mx-auto">
        <Skeleton class="h-4 w-full" />
        <Skeleton class="h-4 w-5/6" />
        <Skeleton class="h-4 w-4/5" />
        <div class="my-6">
            <Skeleton class="h-64 w-full rounded-xl" />
        </div>
        <Skeleton class="h-4 w-full" />
        <Skeleton class="h-4 w-3/4" />
        <div class="grid grid-cols-2 gap-4 my-6">
            <Skeleton class="h-32 w-full rounded-lg" />
            <Skeleton class="h-32 w-full rounded-lg" />
        </div>
        <Skeleton class="h-4 w-full" />
    </div>
</div>
