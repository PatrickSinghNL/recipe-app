<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Clock, Users, ArrowLeft } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import recipesRoutes from '@/routes/recipes';

defineProps<{
    recipe: any;
}>();

const formatTime = (minutes: number) => {
    if (minutes < 60) return `${minutes}m`;
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
};
</script>

<template>
    <Head :title="recipe.name" />

    <PublicLayout>
        <div class="mx-auto max-w-4xl space-y-8">
            <Link :href="recipesRoutes.index.url()">
                <Button variant="ghost" class="mb-4">
                    <ArrowLeft class="mr-2 h-4 w-4" /> Back to Recipes
                </Button>
            </Link>

            <div class="grid gap-8 md:grid-cols-2">
                <div class="aspect-square overflow-hidden rounded-xl bg-muted">
                    <img
                        v-if="recipe.image"
                        :src="`/storage/${recipe.image}`"
                        :alt="recipe.name"
                        class="h-full w-full object-cover"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center bg-accent text-accent-foreground">
                        <span class="text-xl font-medium">No Image</span>
                    </div>
                </div>

                <div class="flex flex-col justify-center space-y-6">
                    <div class="space-y-2">
                        <h1 class="text-4xl font-bold tracking-tight">{{ recipe.name }}</h1>
                        <div class="flex items-center gap-4 text-muted-foreground">
                            <div class="flex items-center gap-1">
                                <Clock class="h-5 w-5" />
                                <span>{{ formatTime(recipe.time) }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <Users class="h-5 w-5" />
                                <span>{{ recipe.number_of_persons }} persons</span>
                            </div>
                        </div>
                    </div>

                    <p class="text-lg leading-relaxed text-muted-foreground">
                        {{ recipe.description }}
                    </p>
                </div>
            </div>

            <div class="grid gap-12 md:grid-cols-2">
                <section class="space-y-4">
                    <h2 class="text-2xl font-bold">Ingredients</h2>
                    <ul class="space-y-3">
                        <li v-for="ingredient in recipe.ingredients" :key="ingredient.id" class="flex items-center justify-between rounded-lg border p-3">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="ingredient.image"
                                    :src="`/storage/${ingredient.image}`"
                                    class="h-10 w-10 rounded object-cover"
                                />
                                <span class="font-medium">{{ ingredient.name }}</span>
                            </div>
                            <span class="text-muted-foreground">{{ ingredient.quantity }}</span>
                        </li>
                    </ul>
                </section>

                <section class="space-y-4">
                    <h2 class="text-2xl font-bold">Supplies</h2>
                    <ul class="space-y-3">
                        <li v-for="supply in recipe.supplies" :key="supply.id" class="flex items-center gap-3 rounded-lg border p-3">
                            <img
                                v-if="supply.image"
                                :src="`/storage/${supply.image}`"
                                class="h-10 w-10 rounded object-cover"
                            />
                            <span class="font-medium">{{ supply.name }}</span>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </PublicLayout>
</template>
