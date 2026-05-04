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
    supplies: any[];
}>();

const isDialogOpen = ref(false);
const editingSupply = ref<any>(null);

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
    editingSupply.value = null;
    form.name = '';
    form.image = null;
    form.clearErrors();
    imagePreview.value = null;
    isDialogOpen.value = true;
};

const openEdit = (supply: any) => {
    editingSupply.value = supply;
    form.name = supply.name;
    form.image = null;
    imagePreview.value = null;
    isDialogOpen.value = true;
};

const submit = () => {
    if (editingSupply.value) {
        form.transform((data) => ({
            ...data,
            _method: 'PUT',
        })).post(admin.supplies.update.url(editingSupply.value.id), {
            onSuccess: () => {
                isDialogOpen.value = false;
            },
        });
    } else {
        form.post(admin.supplies.store.url(), {
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
    router.post(admin.supplies.destroy.url(deleteId.value), { _method: 'DELETE' }, {
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
    <Head title="Manage Supplies" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Supplies</h1>
                <p class="text-muted-foreground">Manage global list of kitchen supplies.</p>
            </div>
            <Button @click="openCreate">
                <Plus class="mr-2 h-4 w-4" /> Add Supply
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
                        <tr v-for="supply in supplies" :key="supply.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="p-4">
                                <img v-if="supply.image" :src="`/storage/${supply.image}`" class="h-10 w-10 rounded object-cover" />
                                <div v-else class="h-10 w-10 rounded bg-muted flex items-center justify-center text-[8px]">No Image</div>
                            </td>
                            <td class="p-4 font-medium">{{ supply.name }}</td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openEdit(supply)">
                                        <Pencil class="h-4 w-4 cursor-pointer" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="confirmDelete(supply.id)">
                                        <Trash2 class="h-4 w-4 text-destructive cursor-pointer" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="supplies.length === 0">
                            <td colspan="3" class="p-8 text-center text-muted-foreground">No supplies found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Dialog v-model:open="isDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editingSupply ? 'Edit Supply' : 'Add Supply' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="sup-name">Name</Label>
                        <Input id="sup-name" v-model="form.name" required />
                    </div>
                    <div class="space-y-2">
                        <Label>Image</Label>
                        <div v-if="imagePreview || (editingSupply && editingSupply.image)" class="mb-2">
                            <img 
                                v-if="imagePreview"
                                :src="imagePreview" 
                                class="h-20 w-20 rounded object-cover border" 
                            />
                            <img 
                                v-else-if="editingSupply && editingSupply.image"
                                :src="`/storage/${editingSupply.image}`" 
                                class="h-20 w-20 rounded object-cover border" 
                            />
                        </div>
                        <Input type="file" @change="handleImageChange" />
                    </div>
                </form>
                <DialogFooter>
                    <Button variant="outline" @click="isDialogOpen = false">Cancel</Button>
                    <Button @click="submit" :disabled="form.processing">
                        {{ editingSupply ? 'Update' : 'Create' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <DeleteConfirmModal
            :open="deleteId !== null"
            title="Delete Supply"
            description="Are you sure you want to delete this supply? This action cannot be undone."
            :loading="deleteLoading"
            @update:open="(v) => { if (!v) deleteId = null; }"
            @confirm="handleDelete"
        />
    </div>
</template>
