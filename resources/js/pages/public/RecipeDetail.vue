<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Clock, Users, ArrowLeft } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import PublicLayout from '@/layouts/PublicLayout.vue';
import recipesRoutes from '@/routes/recipes';

defineProps<{
    recipe: any;
}>();

const formatTime = (minutes: number) => {
    if (minutes < 60) {
return `${minutes}m`;
}

    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
};
</script>

<template>
    <Head :title="recipe.name" />

    <PublicLayout>
        <div class="mx-auto max-w-5xl space-y-8">
            <Link :href="recipesRoutes.index.url()">
                <Button variant="ghost" class="group -ml-2 text-muted-foreground hover:text-primary mb-4">
                    <ArrowLeft class="mr-2 h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    Back to Recipes
                </Button>
            </Link>

            <div class="grid gap-12 lg:grid-cols-2">
                <!-- Image Section -->
                <div class="space-y-6">
                    <div class="aspect-square overflow-hidden rounded-3xl bg-muted shadow-lg">
                        <img
                            v-if="recipe.image"
                            :src="`/storage/${recipe.image}`"
                            :alt="recipe.name"
                            class="h-full w-full object-cover"
                        />
                        <div v-else class="flex h-full w-full items-center justify-center bg-accent/20 text-accent-foreground">
                            <span class="text-lg">No Image Available</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-8 rounded-2xl bg-muted/30 p-6">
                        <div class="text-center">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-bold">Prep Time</p>
                            <div class="mt-1 flex items-center justify-center gap-2 text-xl font-bold">
                                <Clock class="h-5 w-5 text-primary" />
                                <span>{{ formatTime(recipe.time) }}</span>
                            </div>
                        </div>
                        <Separator orientation="vertical" class="h-8" />
                        <div class="text-center">
                            <p class="text-xs text-muted-foreground uppercase tracking-widest font-bold">Servings</p>
                            <div class="mt-1 flex items-center justify-center gap-2 text-xl font-bold">
                                <Users class="h-5 w-5 text-primary" />
                                <span>{{ recipe.number_of_persons }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Section -->
                <div class="space-y-8">
                    <div class="space-y-4">
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="category in recipe.categories" :key="category.id" variant="outline" class="rounded-full border-primary/30 bg-primary/5 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-primary">
                                {{ category.name }}
                            </Badge>
                        </div>
                        <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl leading-tight text-foreground">{{ recipe.name }}</h1>
                        <p class="text-lg leading-relaxed text-muted-foreground">{{ recipe.description }}</p>
                    </div>

                    <Separator />

                    <div class="space-y-6">
                        <h2 class="text-2xl font-bold tracking-tight">Ingredients</h2>
                        <ul class="grid gap-4 sm:grid-cols-2">
                            <li v-for="ingredient in recipe.ingredients" :key="ingredient.id" class="flex items-center gap-4 rounded-2xl border border-border/40 bg-card/50 p-4 shadow-sm backdrop-blur-sm transition-all hover:border-primary/40 hover:shadow-md">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary font-bold">
                                    {{ ingredient.pivot?.quantity?.match(/\d+/)?.[0] || '1' }}
                                </div>
                                <div>
                                    <p class="font-bold text-sm leading-tight">{{ ingredient.name }}</p>
                                    <p class="text-[11px] text-muted-foreground font-medium uppercase tracking-wider mt-0.5">{{ ingredient.pivot?.quantity || ingredient.quantity }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="space-y-6">
                        <h2 class="text-2xl font-bold tracking-tight">Supplies Needed</h2>
                        <div class="flex flex-wrap gap-3">
                            <Badge v-for="supply in recipe.supplies" :key="supply.id" variant="secondary" class="rounded-full px-5 py-2 text-xs font-semibold shadow-sm transition-colors hover:bg-primary hover:text-primary-foreground">
                                {{ supply.name }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
