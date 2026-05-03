<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex items-center" v-bind="$attrs">
        <div
            class="mr-3 flex aspect-square size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground shadow-sm ring-1 ring-white/10"
        >
            <AppLogoIcon class="size-5" />
        </div>
        <div class="ml-1 grid flex-1 text-left text-sm">
            <span class="mb-0.5 truncate leading-tight font-semibold">Recipe App</span>
        </div>
    </div>
</template>
