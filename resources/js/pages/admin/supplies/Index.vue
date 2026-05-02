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
    supplies: any[];
}>();

const isDialogOpen = ref(false);
const editingSupply = ref<any>(null);

const form = useForm({
    name: '',
    image: null as File | null,
});

const openCreate = () => {
    editingSupply.value = null;
    form.reset();
    isDialogOpen.value = true;
};

const openEdit = (supply: any) => {
    editingSupply.value = supply;
    form.name = supply.name;
    form.image = null;
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

const deleteSupply = (id: number) => {
    if (confirm('Are you sure?')) {
        router.delete(admin.supplies.destroy.url(id));
    }
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
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="icon" @click="deleteSupply(supply.id)">
                                    <Trash2 class="h-4 w-4 text-destructive" />
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
                        <Input type="file" @input="form.image = $event.target.files[0]" />
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
    </div>
</template>
