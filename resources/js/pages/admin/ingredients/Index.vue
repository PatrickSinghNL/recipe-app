<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Plus, Pencil, Trash2, Save, X } from 'lucide-vue-next';
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import admin from '@/routes/admin';

const props = defineProps<{
    ingredients: any[];
}>();

const isDialogOpen = ref(false);
const editingIngredient = ref<any>(null);

const form = useForm({
    name: '',
    quantity: '',
    price: '',
    image: null as File | null,
});

const openCreate = () => {
    editingIngredient.value = null;
    form.reset();
    isDialogOpen.value = true;
};

const openEdit = (ingredient: any) => {
    editingIngredient.value = ingredient;
    form.name = ingredient.name;
    form.quantity = ingredient.quantity ?? '';
    form.price = ingredient.price ?? '';
    form.image = null;
    isDialogOpen.value = true;
};

const submit = () => {
    if (editingIngredient.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(admin.ingredients.update.url(editingIngredient.value.id), {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
        });
    } else {
        form.post(admin.ingredients.store.url(), {
            onSuccess: () => {
                isDialogOpen.value = false;
                form.reset();
            },
        });
    }
};

const deleteId = ref<number | null>(null);
const deleteLoading = ref(false);

const confirmDelete = (id: number) => {
    deleteId.value = id;
};

const handleDelete = () => {
    if (!deleteId.value) return;
    deleteLoading.value = true;
    router.delete(admin.ingredients.destroy.url(deleteId.value), {
        onFinish: () => {
            deleteLoading.value = false;
            deleteId.value = null;
        },
    });
};

defineOptions({
    layout: AppLayout,
});
</script>

<template>
    <Head title="Manage Ingredients" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Ingredients</h1>
                <p class="text-muted-foreground">Manage global list of ingredients.</p>
            </div>
            <Button @click="openCreate">
                <Plus class="mr-2 h-4 w-4" /> Add Ingredient
            </Button>
        </div>

        <div class="rounded-md border bg-card">
            <table class="w-full text-sm">
                <thead class="border-b">
                    <tr>
                        <th class="h-12 px-4 text-left font-medium text-muted-foreground w-[80px]">Image</th>
                        <th class="h-12 px-4 text-left font-medium text-muted-foreground">Name</th>
                        <th class="h-12 px-4 text-left font-medium text-muted-foreground">Quantity (default)</th>
                        <th class="h-12 px-4 text-left font-medium text-muted-foreground">Price</th>
                        <th class="h-12 px-4 text-right font-medium text-muted-foreground">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="ingredient in ingredients" :key="ingredient.id" class="border-b transition-colors hover:bg-muted/50">
                        <td class="p-4">
                            <img v-if="ingredient.image" :src="`/storage/${ingredient.image}`" class="h-10 w-10 rounded object-cover" />
                            <div v-else class="h-10 w-10 rounded bg-muted flex items-center justify-center text-[8px]">No Image</div>
                        </td>
                        <td class="p-4 font-medium">{{ ingredient.name }}</td>
                        <td class="p-4 text-muted-foreground">{{ ingredient.quantity || '-' }}</td>
                        <td class="p-4 text-muted-foreground">{{ ingredient.price ? '$' + ingredient.price : '-' }}</td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <Button variant="ghost" size="icon" @click="openEdit(ingredient)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="icon" @click="confirmDelete(ingredient.id)">
                                    <Trash2 class="h-4 w-4 text-destructive" />
                                </Button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="ingredients.length === 0">
                        <td colspan="5" class="p-8 text-center text-muted-foreground">No ingredients found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Dialog v-model:open="isDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingIngredient ? 'Edit Ingredient' : 'Add Ingredient' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="ing-name">Name</Label>
                        <Input id="ing-name" v-model="form.name" required />
                    </div>
                    <div class="space-y-2">
                        <Label for="ing-qty">Quantity / Unit</Label>
                        <Input id="ing-qty" v-model="form.quantity" placeholder="e.g. 200g, 1 piece" />
                    </div>
                    <div class="space-y-2">
                        <Label for="ing-price">Price</Label>
                        <Input id="ing-price" type="number" step="0.01" v-model="form.price" />
                    </div>
                    <div class="space-y-2">
                        <Label>Image</Label>
                        <Input type="file" @input="form.image = $event.target.files[0]" />
                    </div>
                </form>
                <DialogFooter>
                    <Button variant="outline" @click="isDialogOpen = false">Cancel</Button>
                    <Button @click="submit" :disabled="form.processing">
                        {{ editingIngredient ? 'Update' : 'Create' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <DeleteConfirmModal
            :open="deleteId !== null"
            title="Delete Ingredient"
            description="Are you sure you want to delete this ingredient? This action cannot be undone."
            :loading="deleteLoading"
            @update:open="(v) => { if (!v) deleteId = null; }"
            @confirm="handleDelete"
        />
    </div>
</template>
