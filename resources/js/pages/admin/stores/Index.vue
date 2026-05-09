<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import admin from '@/routes/admin';

defineProps<{
    stores: any[];
}>();

const isDialogOpen = ref(false);
const editingStore = ref<any>(null);

const form = useForm({
    name: '',
    image: null as File | null,
});

const imagePreview = ref<string | null>(null);

const handleImageChange = (event: any) => {
    const file = event.target.files[0];
    form.image = file;

    if (file) {
        imagePreview.value = URL.createObjectURL(file);
    } else {
        imagePreview.value = null;
    }
};

const openCreate = () => {
    editingStore.value = null;
    form.name = '';
    form.image = null;
    form.clearErrors();
    imagePreview.value = null;
    isDialogOpen.value = true;
};

const openEdit = (store: any) => {
    editingStore.value = store;
    form.name = store.name;
    form.image = null;
    imagePreview.value = null;
    isDialogOpen.value = true;
};

const submit = () => {
    if (editingStore.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(admin.stores.update.url(editingStore.value.id), {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
        });
    } else {
        form.post(admin.stores.store.url(), {
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
    if (!deleteId.value) {
        return;
    }

    deleteLoading.value = true;
    router.post(admin.stores.destroy.url(deleteId.value), { _method: 'DELETE' }, {
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
    <Head title="Manage Stores" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Stores</h1>
                <p class="text-muted-foreground">Manage supermarkets and shops.</p>
            </div>
            <Button @click="openCreate" class="w-full sm:w-auto">
                <Plus class="mr-2 h-4 w-4" /> Add Store
            </Button>
        </div>

        <div class="rounded-md border bg-card">
            <div class="relative w-full overflow-auto">
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground w-[80px]">Image</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Name</th>
                            <th class="h-12 px-4 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="store in stores" :key="store.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="p-4">
                                <img v-if="store.image" :src="`/storage/${store.image}`" class="h-10 w-10 rounded object-cover" />
                                <div v-else class="h-10 w-10 rounded bg-muted flex items-center justify-center text-[8px]">No Image</div>
                            </td>
                            <td class="p-4 font-medium">{{ store.name }}</td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openEdit(store)">
                                        <Pencil class="h-4 w-4 cursor-pointer" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="confirmDelete(store.id)">
                                        <Trash2 class="h-4 w-4 text-destructive cursor-pointer" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="stores.length === 0">
                            <td colspan="3" class="p-8 text-center text-muted-foreground">No stores found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog v-model:open="isDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingStore ? 'Edit Store' : 'Add Store' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="store-name">Name</Label>
                        <Input id="store-name" v-model="form.name" required />
                    </div>
                    <div class="space-y-2">
                        <Label>Image</Label>
                        <div v-if="imagePreview || (editingStore && editingStore.image)" class="mb-2">
                            <img 
                                v-if="imagePreview"
                                :src="imagePreview" 
                                class="h-20 w-20 rounded object-cover border" 
                            />
                            <img 
                                v-else-if="editingStore && editingStore.image"
                                :src="`/storage/${editingStore.image}`" 
                                class="h-20 w-20 rounded object-cover border" 
                            />
                        </div>
                        <Input type="file" @change="handleImageChange" />
                    </div>
                </form>
                <DialogFooter>
                    <Button variant="outline" @click="isDialogOpen = false">Cancel</Button>
                    <Button @click="submit" :disabled="form.processing">
                        {{ editingStore ? 'Update' : 'Create' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <DeleteConfirmModal
            :open="deleteId !== null"
            title="Delete Store"
            description="Are you sure you want to delete this store? Ingredients associated with it will no longer have a store assigned."
            :loading="deleteLoading"
            @update:open="(v) => { if (!v) deleteId = null; }"
            @confirm="handleDelete"
        />
    </div>
</template>
