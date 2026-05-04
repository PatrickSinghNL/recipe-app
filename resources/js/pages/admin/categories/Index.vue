<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import admin from '@/routes/admin';

const props = defineProps<{
    categories: any[];
}>();

const isDialogOpen = ref(false);
const editingCategory = ref<any>(null);

const form = useForm({
    name: '',
});

const openCreate = () => {
    editingCategory.value = null;
    form.reset();
    isDialogOpen.value = true;
};

const openEdit = (category: any) => {
    editingCategory.value = category;
    form.name = category.name;
    isDialogOpen.value = true;
};

const submit = () => {
    if (editingCategory.value) {
        form.put(admin.categories.update.url(editingCategory.value.id), {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
        });
    } else {
        form.post(admin.categories.store.url(), {
            onSuccess: () => {
                isDialogOpen.value = false;
                form.reset();
            },
        });
    }
};

const deleteCategory = (id: number) => {
    if (confirm('Are you sure?')) {
        router.delete(admin.categories.destroy.url(id));
    }
};

defineOptions({
    layout: AppLayout,
});
</script>

<template>
    <Head title="Manage Categories" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Categories</h1>
                <p class="text-muted-foreground">Manage categories for recipes.</p>
            </div>
            <Button @click="openCreate">
                <Plus class="mr-2 h-4 w-4" /> Add Category
            </Button>
        </div>

        <div class="rounded-md border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b">
                    <tr>
                        <th class="h-12 px-4 text-left font-medium text-muted-foreground">Name</th>
                        <th class="h-12 px-4 text-left font-medium text-muted-foreground">Slug</th>
                        <th class="h-12 px-4 text-right font-medium text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in categories" :key="category.id" class="border-b transition-colors hover:bg-muted/50">
                        <td class="p-4 font-medium">{{ category.name }}</td>
                        <td class="p-4 text-muted-foreground">{{ category.slug }}</td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <Button variant="ghost" size="icon" @click="openEdit(category)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="icon" @click="deleteCategory(category.id)">
                                    <Trash2 class="h-4 w-4 text-destructive" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="categories.length === 0">
                        <td colspan="3" class="p-8 text-center text-muted-foreground">No categories found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Dialog v-model:open="isDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingCategory ? 'Edit Category' : 'Add Category' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="cat-name">Name</Label>
                        <Input id="cat-name" v-model="form.name" required />
                        <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                    </div>
                </form>
                <DialogFooter>
                    <Button variant="outline" @click="isDialogOpen = false">Cancel</Button>
                    <Button @click="submit" :disabled="form.processing">
                        {{ editingCategory ? 'Update' : 'Create' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
