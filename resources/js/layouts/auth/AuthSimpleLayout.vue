<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from '@/components/AppLogo.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10 overflow-hidden"
    >
        <!-- Background Decorations -->
        <div class="absolute -top-24 -left-24 h-96 w-96 rounded-full bg-primary/10 blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 h-96 w-96 rounded-full bg-primary/10 blur-3xl"></div>

        <div class="relative w-full max-w-md z-10">
            <Card class="border-border/40 bg-card/60 shadow-2xl backdrop-blur-xl">
                <CardHeader class="space-y-6 text-center pb-2">
                    <div class="flex flex-col items-center gap-4">
                        <Link
                            :href="home()"
                            class="flex flex-col items-center gap-2 font-medium"
                        >
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-2xl shadow-xl"
                            >
                                <AppLogo only-icon class="size-12" />
                            </div>
                        </Link>
                        <div class="space-y-1.5">
                            <CardTitle class="text-2xl font-bold tracking-tight">{{ title }}</CardTitle>
                            <CardDescription class="text-sm text-muted-foreground">
                                {{ description }}
                            </CardDescription>
                        </div>
                    </div>
                </CardHeader>
                <CardContent class="pt-6">
                    <slot />
                </CardContent>
            </Card>
        </div>
    </div>
</template>
