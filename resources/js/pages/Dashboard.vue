<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, LayoutGrid } from 'lucide-vue-next';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes';
import admin from '@/routes/admin';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <h1 class="text-3xl font-bold">Admin Dashboard</h1>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Recipes</CardTitle>
                    <BookOpen class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">1</div>
                    <p class="text-xs text-muted-foreground">Manage your collection</p>
                </CardContent>
            </Card>
            <!-- Add more stats as needed -->
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Link :href="admin.recipes.index.url()">
                <Card class="hover:bg-muted/50 transition-colors cursor-pointer h-full">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <BookOpen class="h-5 w-5" /> Recipes
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">Create, edit and publish your recipes.</p>
                    </CardContent>
                </Card>
            </Link>

            <Link :href="admin.ingredients.index.url()">
                <Card class="hover:bg-muted/50 transition-colors cursor-pointer h-full">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <LayoutGrid class="h-5 w-5" /> Ingredients
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">Manage the global list of ingredients.</p>
                    </CardContent>
                </Card>
            </Link>

            <Link :href="admin.supplies.index.url()">
                <Card class="hover:bg-muted/50 transition-colors cursor-pointer h-full">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <LayoutGrid class="h-5 w-5" /> Supplies
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">Manage the global list of kitchen supplies.</p>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </div>
</template>
