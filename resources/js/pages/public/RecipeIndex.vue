<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Clock, Users, Heart } from 'lucide-vue-next';
import { useFavoritesStore } from '@/stores/useFavoritesStore';
import { Button } from '@/components/ui/button';
import recipesRoutes from '@/routes/recipes';

defineProps<{
    recipes: any[];
    filters: any;
}>();

const favorites = useFavoritesStore();

const formatTime = (minutes: number) => {
    if (minutes < 60) return `${minutes}m`;
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
};
</script>

<template>
    <Head title="Browse Recipes" />

    <PublicLayout>
        <div class="space-y-8">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Delicious Recipes</h1>
                    <p class="text-muted-foreground">Find your next favorite meal here.</p>
                </div>
            </div>

            <div v-if="recipes.length === 0" class="flex h-[400px] flex-col items-center justify-center rounded-lg border border-dashed">
                <p class="text-lg font-medium text-muted-foreground">No recipes found.</p>
            </div>

            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <Card v-for="recipe in recipes" :key="recipe.id" class="overflow-hidden transition-all hover:shadow-lg">
                    <div class="relative">
                        <Link :href="recipesRoutes.show.url(recipe.id)">
                            <div class="aspect-video w-full overflow-hidden bg-muted">
                                <img
                                    v-if="recipe.image"
                                    :src="`/storage/${recipe.image}`"
                                    :alt="recipe.name"
                                    class="h-full w-full object-cover transition-transform hover:scale-105"
                                />
                                <div v-else class="flex h-full w-full items-center justify-center bg-accent text-accent-foreground">
                                    <span class="text-sm">No Image</span>
                                </div>
                            </div>
                        </Link>
                        <Button
                            variant="secondary"
                            size="icon"
                            class="absolute right-2 top-2 h-8 w-8 rounded-full shadow-sm"
                            @click="favorites.toggleFavorite(recipe.id)"
                        >
                            <Heart
                                class="h-4 w-4"
                                :class="{ 'fill-red-500 text-red-500': favorites.isFavorite(recipe.id) }"
                            />
                        </Button>
                    </div>
                    <CardHeader class="p-4">
                        <CardTitle class="line-clamp-1 text-xl">
                            <Link :href="recipesRoutes.show.url(recipe.id)" class="hover:underline">
                                {{ recipe.name }}
                            </Link>
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="px-4 pb-4">
                        <p class="line-clamp-2 text-sm text-muted-foreground">
                            {{ recipe.description }}
                        </p>
                    </CardContent>
                    <CardFooter class="flex items-center justify-between border-t p-4 text-sm">
                        <div class="flex items-center gap-1 text-muted-foreground">
                            <Clock class="h-4 w-4" />
                            <span>{{ formatTime(recipe.time) }}</span>
                        </div>
                        <div class="flex items-center gap-1 text-muted-foreground">
                            <Users class="h-4 w-4" />
                            <span>{{ recipe.number_of_persons }}</span>
                        </div>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </PublicLayout>
</template>
