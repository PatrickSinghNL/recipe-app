<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Clock, Users, Heart } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import PublicLayout from '@/layouts/PublicLayout.vue';
import recipesRoutes from '@/routes/recipes';
import { useFavoritesStore } from '@/stores/useFavoritesStore';

defineProps<{
    recipes: any[];
    categories: any[];
    filters: any;
}>();

const favorites = useFavoritesStore();

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
    <Head title="Browse Recipes" />

    <PublicLayout>
        <div class="space-y-12">
            <!-- Hero Section -->
            <section class="relative overflow-hidden rounded-3xl bg-primary px-6 py-16 text-primary-foreground sm:px-12 sm:py-24">
                <div class="relative z-10 mx-auto max-w-2xl text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight sm:text-6xl">
                        Discover Your Next <span class="text-white">Culinary Masterpiece</span>
                    </h1>
                    <p class="mt-6 text-lg leading-8 opacity-90">
                        Explore hundreds of hand-picked recipes from around the world. Simple, healthy, and delicious meals at your fingertips.
                    </p>
                </div>
                <!-- Decorative background blob -->
                <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-16 -left-16 h-64 w-64 rounded-full bg-black/10 blur-3xl"></div>
            </section>

            <div class="space-y-8">
                <!-- Category Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        :href="recipesRoutes.index.url()"
                        :class="[
                            'rounded-full px-5 py-2 text-sm font-semibold transition-all duration-300',
                            !filters.category ? 'bg-primary text-primary-foreground shadow-lg scale-105' : 'bg-muted text-muted-foreground hover:bg-muted/80'
                        ]"
                    >
                        All Recipes
                    </Link>
                    <Link
                        v-for="category in categories"
                        :key="category.id"
                        :href="recipesRoutes.index.url({ query: { category: category.slug } })"
                        :class="[
                            'rounded-full px-5 py-2 text-sm font-semibold transition-all duration-300',
                            filters.category === category.slug ? 'bg-primary text-primary-foreground shadow-lg scale-105' : 'bg-muted text-muted-foreground hover:bg-muted/80'
                        ]"
                    >
                        {{ category.name }}
                    </Link>
                </div>
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold tracking-tight">Recent Recipes</h2>
                    <Badge variant="secondary" class="rounded-full px-4 py-1">{{ recipes.length }} Recipes</Badge>
                </div>

                <div v-if="recipes.length === 0" class="flex h-[400px] flex-col items-center justify-center rounded-3xl border-2 border-dashed bg-muted/30">
                    <p class="text-lg font-medium text-muted-foreground">No recipes found.</p>
                </div>

                <div v-else class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <Card v-for="recipe in recipes" :key="recipe.id" class="group overflow-hidden rounded-2xl border-none bg-card shadow-md transition-all hover:-translate-y-1 hover:shadow-xl dark:bg-muted/50">
                        <div class="relative overflow-hidden">
                            <Link :href="recipesRoutes.show.url(recipe.id)">
                                <div class="aspect-[4/3] w-full overflow-hidden bg-muted">
                                    <img
                                        v-if="recipe.image"
                                        :src="`/storage/${recipe.image}`"
                                        :alt="recipe.name"
                                        class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                                    />
                                    <div v-else class="flex h-full w-full items-center justify-center bg-accent/20 text-accent-foreground">
                                        <span class="text-sm font-medium">Coming Soon</span>
                                    </div>
                                </div>
                            </Link>
                            <div class="absolute inset-0 bg-linear-to-t from-black/60 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                            <div class="absolute bottom-3 left-3 flex flex-wrap gap-1">
                                <Badge v-for="category in recipe.categories" :key="category.id" variant="secondary" class="bg-white/90 text-[10px] font-bold uppercase tracking-wider text-primary backdrop-blur-sm dark:bg-black/50 dark:text-white">
                                    {{ category.name }}
                                </Badge>
                            </div>
                            <Button
                                variant="secondary"
                                size="icon"
                                class="absolute right-3 top-3 h-9 w-9 rounded-full bg-white/90 shadow-sm backdrop-blur-sm transition-transform hover:scale-110 active:scale-95 dark:bg-black/50"
                                @click="favorites.toggleFavorite(recipe.id)"
                            >
                                <Heart
                                    class="h-5 w-5"
                                    :class="{ 'fill-red-500 text-red-500': favorites.isFavorite(recipe.id) }"
                                />
                            </Button>
                        </div>
                        <CardHeader class="p-5">
                            <CardTitle class="line-clamp-1 text-xl font-bold transition-colors group-hover:text-primary">
                                <Link :href="recipesRoutes.show.url(recipe.id)">
                                    {{ recipe.name }}
                                </Link>
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="px-5 pb-5">
                            <p class="line-clamp-2 text-sm leading-relaxed text-muted-foreground">
                                {{ recipe.description }}
                            </p>
                        </CardContent>
                        <CardFooter class="flex items-center justify-between border-t bg-muted/20 px-5 py-3 text-xs font-medium">
                            <div class="flex items-center gap-1.5 text-foreground/70">
                                <Clock class="h-3.5 w-3.5" />
                                <span>{{ formatTime(recipe.time) }}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-foreground/70">
                                <Users class="h-3.5 w-3.5" />
                                <span>{{ recipe.number_of_persons }} Servings</span>
                            </div>
                        </CardFooter>
                    </Card>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
