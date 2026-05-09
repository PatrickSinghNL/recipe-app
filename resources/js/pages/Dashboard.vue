<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, LayoutGrid, Store, ChevronRight } from 'lucide-vue-next';
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

defineProps<{
    stats: {
        total_recipes: number;
        total_ingredients: number;
        total_supplies: number;
        total_stores: number;
    };
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-6">
        <h1 class="text-3xl font-bold">Admin Dashboard</h1>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Ingredients</CardTitle>
                    <LayoutGrid class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.total_ingredients }}</div>
                    <p class="text-xs text-muted-foreground">Global list items</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Supplies</CardTitle>
                    <LayoutGrid class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.total_supplies }}</div>
                    <p class="text-xs text-muted-foreground">Kitchen equipment</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">Total Stores</CardTitle>
                    <Store class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <div class="text-2xl font-bold">{{ stats.total_stores }}</div>
                    <p class="text-xs text-muted-foreground">Supermarkets & shops</p>
                </CardContent>
            </Card>
            <!-- Add more stats as needed -->
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <Link :href="admin.recipes.index.url()" class="h-full">
                <Card class="group hover:border-primary/50 hover:shadow-lg transition-all cursor-pointer h-full bg-card/50">
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <BookOpen class="h-5 w-5 text-primary" /> 
                                <span>Recipes</span>
                            </div>
                            <ChevronRight class="h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-primary" />
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">Create, edit and publish your recipes.</p>
                    </CardContent>
                </Card>
            </Link>

            <Link :href="admin.ingredients.index.url()" class="h-full">
                <Card class="group hover:border-primary/50 hover:shadow-lg transition-all cursor-pointer h-full bg-card/50">
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <LayoutGrid class="h-5 w-5 text-primary" /> 
                                <span>Ingredients</span>
                            </div>
                            <ChevronRight class="h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-primary" />
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">Manage the global list of ingredients.</p>
                    </CardContent>
                </Card>
            </Link>

            <Link :href="admin.supplies.index.url()" class="h-full">
                <Card class="group hover:border-primary/50 hover:shadow-lg transition-all cursor-pointer h-full bg-card/50">
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <LayoutGrid class="h-5 w-5 text-primary" /> 
                                <span>Supplies</span>
                            </div>
                            <ChevronRight class="h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-primary" />
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">Manage the global list of kitchen supplies.</p>
                    </CardContent>
                </Card>
            </Link>

            <Link :href="admin.stores.index.url()" class="h-full">
                <Card class="group hover:border-primary/50 hover:shadow-lg transition-all cursor-pointer h-full bg-card/50">
                    <CardHeader>
                        <CardTitle class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <Store class="h-5 w-5 text-primary" /> 
                                <span>Stores</span>
                            </div>
                            <ChevronRight class="h-4 w-4 text-muted-foreground transition-transform group-hover:translate-x-1 group-hover:text-primary" />
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">Manage supermarkets and shopping locations.</p>
                    </CardContent>
                </Card>
            </Link>
        </div>
    </div>
</template>
