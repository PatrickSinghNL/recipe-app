<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Search, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import admin from '@/routes/admin';

const props = defineProps<{
    ingredients: any[];
    stores: any[];
}>();

const isDialogOpen = ref(false);
const editingIngredient = ref<any>(null);

const form = useForm({
    name: '',
    quantity: '',
    price: '',
    image: null as File | null,
    store_id: '' as string | number,
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
    editingIngredient.value = null;
    form.name = '';
    form.quantity = '';
    form.price = '';
    form.store_id = 'none';
    form.image = null;
    form.clearErrors();
    imagePreview.value = null;
    isDialogOpen.value = true;
};

const openEdit = (ingredient: any) => {
    editingIngredient.value = ingredient;
    form.name = ingredient.name;
    form.quantity = ingredient.quantity ?? '';
    form.price = ingredient.price ?? '';
    form.store_id = ingredient.store_id ? ingredient.store_id.toString() : 'none';
    form.image = null;
    imagePreview.value = null;
    isDialogOpen.value = true;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: editingIngredient.value ? 'PUT' : undefined,
        store_id: data.store_id === 'none' || data.store_id === '' ? null : data.store_id,
    })).post(editingIngredient.value ? admin.ingredients.update.url(editingIngredient.value.id) : admin.ingredients.store.url(), {
        onSuccess: () => {
            isDialogOpen.value = false;

            if (!editingIngredient.value) {
                form.reset();
            }
        },
    });
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
    router.post(admin.ingredients.destroy.url(deleteId.value), { _method: 'DELETE' }, {
        onFinish: () => {
            deleteLoading.value = false;
            deleteId.value = null;
        },
    });
};

// Filtering & Pagination
const search = ref('');
const activeStoreId = ref<number | null>(null);
const currentPage = ref(1);
const pageSize = 10;

const filteredIngredients = computed(() => {
    return props.ingredients.filter(ingredient => {
        const matchesSearch = ingredient.name.toLowerCase().includes(search.value.toLowerCase());
        const matchesStore = activeStoreId.value === null || ingredient.store_id === activeStoreId.value;

        return matchesSearch && matchesStore;
    });
});

const totalPages = computed(() => Math.ceil(filteredIngredients.value.length / pageSize));

const paginatedIngredients = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    const end = start + pageSize;

    return filteredIngredients.value.slice(start, end);
});

watch([search, activeStoreId], () => {
    currentPage.value = 1;
});

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
            <div class="flex gap-2">
                <div class="relative w-64">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Search ingredients..."
                        class="pl-9"
                    />
                </div>
                <Button @click="openCreate">
                    <Plus class="mr-2 h-4 w-4" /> Add Ingredient
                </Button>
            </div>
        </div>

        <div class="flex items-center gap-1 overflow-x-auto pb-1 scrollbar-hide">
            <Button
                variant="ghost"
                size="sm"
                :class="[
                    'rounded-full px-4 h-8 transition-all',
                    activeStoreId === null ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'hover:bg-muted'
                ]"
                @click="activeStoreId = null"
            >
                All
            </Button>
            <Button
                v-for="store in stores"
                :key="store.id"
                variant="ghost"
                size="sm"
                :class="[
                    'rounded-full px-4 h-8 flex items-center gap-2 transition-all',
                    activeStoreId === store.id ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'hover:bg-muted'
                ]"
                @click="activeStoreId = store.id"
            >
                <img v-if="store.image" :src="`/storage/${store.image}`" class="h-4 w-4 rounded-full object-cover" />
                {{ store.name }}
            </Button>
        </div>

        <div class="rounded-md border bg-card">
            <div class="relative w-full overflow-auto">
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground w-[80px]">Image</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Name</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Store</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Quantity (default)</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Price</th>
                            <th class="h-12 px-4 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ingredient in paginatedIngredients" :key="ingredient.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="p-4">
                                <img v-if="ingredient.image" :src="`/storage/${ingredient.image}`" class="h-10 w-10 rounded object-cover" />
                                <div v-else class="h-10 w-10 rounded bg-muted flex items-center justify-center text-[8px]">No Image</div>
                            </td>
                            <td class="p-4 font-medium">{{ ingredient.name }}</td>
                            <td class="p-4 text-muted-foreground">
                                <div v-if="ingredient.store" class="flex items-center gap-2">
                                    <img v-if="ingredient.store.image" :src="`/storage/${ingredient.store.image}`" class="h-5 w-5 rounded object-cover" />
                                    <span>{{ ingredient.store.name }}</span>
                                </div>
                                <span v-else>-</span>
                            </td>
                            <td class="p-4 text-muted-foreground">{{ ingredient.quantity || '-' }}</td>
                            <td class="p-4 text-muted-foreground">{{ ingredient.price ? $page.props.settings.currency_symbol + ingredient.price : '-' }}</td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="ghost" size="icon" @click="openEdit(ingredient)">
                                        <Pencil class="h-4 w-4 cursor-pointer" />
                                    </Button>
                                    <Button variant="ghost" size="icon" @click="confirmDelete(ingredient.id)">
                                        <Trash2 class="h-4 w-4 text-destructive cursor-pointer" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="paginatedIngredients.length === 0">
                            <td colspan="6" class="p-8 text-center text-muted-foreground">No ingredients found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="totalPages > 1" class="flex items-center justify-between px-2">
            <div class="text-sm text-muted-foreground">
                Showing {{ (currentPage - 1) * pageSize + 1 }} to {{ Math.min(currentPage * pageSize, filteredIngredients.length) }} of {{ filteredIngredients.length }} ingredients
            </div>
            <div class="flex items-center space-x-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage === 1"
                    @click="currentPage--"
                >
                    <ChevronLeft class="h-4 w-4 mr-1" /> Previous
                </Button>
                <div class="flex items-center gap-1">
                    <Button
                        v-for="page in totalPages"
                        :key="page"
                        variant="ghost"
                        size="sm"
                        :class="['w-8 h-8 p-0', currentPage === page ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'hover:bg-muted']"
                        @click="currentPage = page"
                    >
                        {{ page }}
                    </Button>
                </div>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage === totalPages"
                    @click="currentPage++"
                >
                    Next <ChevronRight class="h-4 w-4 ml-1" />
                </Button>
            </div>
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
                        <Label>Store</Label>
                        <Select v-model="form.store_id">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a store" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">None</SelectItem>
                                <SelectItem v-for="store in stores" :key="store.id" :value="store.id.toString()">
                                    {{ store.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.store_id" class="text-sm text-destructive">{{ form.errors.store_id }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label>Image</Label>
                        <div v-if="imagePreview || (editingIngredient && editingIngredient.image)" class="mb-2">
                            <img 
                                v-if="imagePreview"
                                :src="imagePreview" 
                                class="h-20 w-20 rounded object-cover border" 
                            />
                            <img 
                                v-else-if="editingIngredient && editingIngredient.image"
                                :src="`/storage/${editingIngredient.image}`" 
                                class="h-20 w-20 rounded object-cover border" 
                            />
                        </div>
                        <Input type="file" @change="handleImageChange" />
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
