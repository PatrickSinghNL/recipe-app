<script setup lang="ts">
defineProps<{
    onlyIcon?: boolean;
}>();
</script>

<template>
    <div class="flex items-center" v-bind="$attrs">
        <div
            class="mr-3 flex aspect-square size-8 items-center justify-center rounded-lg shadow-sm ring-1 ring-white/10 overflow-hidden shrink-0"
        >
            <!-- Dark mode icon (default if no .dark class) -->
            <img src="/favicon.png" class="hidden dark:block size-full object-cover" alt="Logo Dark" />
            <!-- Light mode icon -->
            <img src="/favicon-light.png" class="block dark:hidden size-full object-cover" alt="Logo Light" />
        </div>
        <div v-if="!onlyIcon" class="ml-1 grid flex-1 text-left text-sm">
            <span class="mb-0.5 truncate leading-tight font-semibold">Recipe App</span>
        </div>
    </div>
</template>
