<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Plus, Pencil, Trash2, Eye } from 'lucide-vue-next';
import admin from '@/routes/admin';
import recipesRoutes from '@/routes/recipes';

defineProps<{
    recipes: any[];
}>();

const deleteRecipe = (id: number) => {
    if (confirm('Are you sure you want to delete this recipe?')) {
        router.delete(admin.recipes.destroy.url(id));
    }
};

defineOptions({
    layout: AppLayout,
});
</script>

<template>
    <Head title="Manage Recipes" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Recipes</h1>
                <p class="text-muted-foreground">Manage your recipe collection.</p>
            </div>
            <Link :href="admin.recipes.create.url()">
                <Button>
                    <Plus class="mr-2 h-4 w-4" /> Add Recipe
                </Button>
            </Link>
        </div>

        <div class="rounded-md border bg-card">
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground w-[100px]">Image</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Name</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Created</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        <tr v-for="recipe in recipes" :key="recipe.id" class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <td class="p-4 align-middle">
                                <img
                                    v-if="recipe.image"
                                    :src="`/storage/${recipe.image}`"
                                    class="h-12 w-12 rounded object-cover"
                                />
                                <div v-else class="h-12 w-12 rounded bg-muted flex items-center justify-center text-[10px]">No Image</div>
                            </td>
                            <td class="p-4 align-middle font-medium">{{ recipe.name }}</td>
                            <td class="p-4 align-middle">
                                <Badge :variant="recipe.is_published ? 'default' : 'secondary'">
                                    {{ recipe.is_published ? 'Published' : 'Draft' }}
                                </Badge>
                            </td>
                            <td class="p-4 align-middle text-muted-foreground">
                                {{ new Date(recipe.created_at).toLocaleDateString() }}
                            </td>
                            <td class="p-4 align-middle text-right">
                                <div class="flex justify-end gap-2">
                                    <Link :href="recipesRoutes.show.url(recipe.id)" target="_blank">
                                        <Button variant="ghost" size="icon">
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Link :href="admin.recipes.edit.url(recipe.id)">
                                        <Button variant="ghost" size="icon">
                                            <Pencil class="h-4 w-4" />
                                        </Button>
                                    </Link>
                                    <Button variant="ghost" size="icon" @click="deleteRecipe(recipe.id)">
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="recipes.length === 0">
                            <td colspan="5" class="p-8 text-center text-muted-foreground">
                                No recipes found. Start by adding one!
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
