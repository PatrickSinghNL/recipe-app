<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Loader2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import MultiSelect from '@/components/ui/multi-select/MultiSelect.vue';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import admin from '@/routes/admin';

const props = defineProps<{
    recipe?: any;
    ingredients: any[];
    supplies: any[];
}>();

const form = useForm({
    name: props.recipe?.name ?? '',
    description: props.recipe?.description ?? '',
    time: props.recipe?.time ?? 30,
    number_of_persons: props.recipe?.number_of_persons ?? 2,
    is_published: props.recipe ? !!props.recipe.is_published : false,
    image: null as File | null,
    ingredients: props.recipe?.ingredients.map((i: any) => i.id) ?? [],
    supplies: props.recipe?.supplies.map((s: any) => s.id) ?? [],
});

const submit = () => {
    if (props.recipe) {
        // Inertia doesn't support file uploads with PUT directly, we use POST with _method=PUT
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(admin.recipes.update.url(props.recipe.id));
    } else {
        form.post(admin.recipes.store.url());
    }
};


defineOptions({
    layout: AppLayout,
});
</script>

<template>
    <Head :title="recipe ? 'Edit Recipe' : 'Add Recipe'" />

    <div class="flex flex-col gap-6 p-6 mx-auto max-w-5xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <Link :href="admin.recipes.index.url()">
                    <Button variant="ghost" size="icon">
                        <ArrowLeft class="h-4 w-4" />
                    </Button>
                </Link>
                <h1 class="text-2xl font-bold tracking-tight">{{ recipe ? 'Edit Recipe' : 'Add Recipe' }}</h1>
            </div>
        </div>

        <form @submit.prevent="submit" class="grid gap-6 md:grid-cols-3">
            <div class="md:col-span-2 space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Basic Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Recipe Name</Label>
                            <Input id="name" v-model="form.name" required placeholder="e.g. Pasta Carbonara" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" rows="5" placeholder="Describe how to make it..." />
                            <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="time">Time (minutes)</Label>
                                <Input id="time" type="number" v-model="form.time" required />
                                <p v-if="form.errors.time" class="text-sm text-destructive">{{ form.errors.time }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="persons">Number of Persons</Label>
                                <Input id="persons" type="number" v-model="form.number_of_persons" required />
                                <p v-if="form.errors.number_of_persons" class="text-sm text-destructive">{{ form.errors.number_of_persons }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Ingredients</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <MultiSelect
                            v-model="form.ingredients"
                            :options="ingredients"
                            placeholder="Search ingredients..."
                        />
                        <p v-if="ingredients.length === 0" class="text-sm text-muted-foreground italic mt-2">No ingredients available. Create them first.</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Supplies</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <MultiSelect
                            v-model="form.supplies"
                            :options="supplies"
                            placeholder="Search supplies..."
                        />
                        <p v-if="supplies.length === 0" class="text-sm text-muted-foreground italic mt-2">No supplies available. Create them first.</p>
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Publishing</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_published" v-model="form.is_published" />
                            <Label for="is_published" class="cursor-pointer">Published</Label>
                        </div>

                        <div class="space-y-2">
                            <Label>Recipe Image</Label>
                            <div class="aspect-video w-full rounded-lg border-2 border-dashed flex items-center justify-center overflow-hidden bg-muted relative">
                                <img
                                    v-if="recipe?.image && !form.image"
                                    :src="`/storage/${recipe.image}`"
                                    class="h-full w-full object-cover"
                                />
                                <div v-else class="text-center p-4">
                                    <span class="text-xs text-muted-foreground">Click to upload image</span>
                                </div>
                                <Input
                                    type="file"
                                    class="absolute inset-0 opacity-0 cursor-pointer"
                                    @input="form.image = $event.target.files[0]"
                                />
                            </div>
                            <p v-if="form.image" class="text-xs text-green-600 font-medium truncate">Selected: {{ form.image.name }}</p>
                            <p v-if="form.errors.image" class="text-sm text-destructive">{{ form.errors.image }}</p>
                        </div>

                        <Button type="submit" class="w-full" :disabled="form.processing">
                            <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                            <Save v-else class="mr-2 h-4 w-4" />
                            {{ recipe ? 'Update Recipe' : 'Save Recipe' }}
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </form>
    </div>
</template>
