<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { Plus, Pencil, Trash2, Search, ChevronLeft, ChevronRight, Wand2, Loader2 } from 'lucide-vue-next';
import { ref, computed, watch, reactive } from 'vue';
import { toast } from 'vue-sonner';
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
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import AppLayout from '@/layouts/AppLayout.vue';
import admin from '@/routes/admin';

interface StoreData {
    store_id: number;
    price: string;
    quantity: string;
    product_url: string;
    image: File | null;
    existingImage: string | null;
    imagePreview: string | null;
}

const props = defineProps<{
    ingredients: any[];
    stores: any[];
}>();

const isDialogOpen = ref(false);
const editingIngredient = ref<any>(null);
const ingredientName = ref('');
const activeStoreTab = ref('');
const isSubmitting = ref(false);

// Per-store data for the dialog
const storeDataMap = ref<Record<number, StoreData>>({});

const initStoreDataMap = () => {
    const map: Record<number, StoreData> = {};
    for (const store of props.stores) {
        map[store.id] = {
            store_id: store.id,
            price: '',
            quantity: '',
            product_url: '',
            image: null,
            existingImage: null,
            imagePreview: null,
        };
    }
    storeDataMap.value = map;
};

const scrapingStoreId = ref<number | null>(null);

const scrapeProduct = async (storeId: number) => {
    const data = storeDataMap.value[storeId];
    if (!data?.product_url) {
        toast.error('Please enter a product URL first.');
        return;
    }

    scrapingStoreId.value = storeId;

    try {
        const response = await fetch(admin.ingredients.scrapeProduct.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({ url: data.product_url }),
        });

        const result = await response.json();

        if (response.ok) {
            if (result.price) {
                data.price = result.price;
            }

            if (result.name && !ingredientName.value) {
                ingredientName.value = result.name;
            }

            if (result.quantity) {
                data.quantity = result.quantity;
            }

            if (result.image_base64) {
                const imgResponse = await fetch(result.image_base64);
                const blob = await imgResponse.blob();

                const mimeType = blob.type;
                let extension = 'jpg';
                if (mimeType === 'image/webp') extension = 'webp';
                else if (mimeType === 'image/png') extension = 'png';
                else if (mimeType === 'image/gif') extension = 'gif';
                else if (mimeType === 'image/avif') extension = 'avif';

                const filename = `product-${Date.now()}.${extension}`;
                const file = new File([blob], filename, { type: mimeType });

                data.image = file;
                data.imagePreview = URL.createObjectURL(file);
                toast.success('Product data and image updated!');
            } else if (result.image) {
                try {
                    const imgResponse = await fetch(result.image);
                    const blob = await imgResponse.blob();
                    const filename = result.image.split('/').pop() || 'product.jpg';
                    const file = new File([blob], filename, { type: blob.type });

                    data.image = file;
                    data.imagePreview = URL.createObjectURL(file);
                    toast.success('Product data and image updated!');
                } catch {
                    toast.success('Price updated! (Image could not be fetched due to security restrictions)');
                }
            } else {
                toast.success('Price updated successfully!');
            }
        } else {
            toast.error(result.error || 'Failed to scrape product data.');
        }
    } catch (error) {
        toast.error('An error occurred while scraping.');
        console.error(error);
    } finally {
        scrapingStoreId.value = null;
    }
};

const handleImageChange = (event: any, storeId: number) => {
    const file = event.target.files[0];
    const data = storeDataMap.value[storeId];
    data.image = file;

    if (file) {
        data.imagePreview = URL.createObjectURL(file);
    } else {
        data.imagePreview = null;
    }
};

const openCreate = () => {
    editingIngredient.value = null;
    ingredientName.value = '';
    initStoreDataMap();
    activeStoreTab.value = props.stores[0]?.id?.toString() || '';
    isDialogOpen.value = true;
};

const openEdit = (ingredient: any) => {
    editingIngredient.value = ingredient;
    ingredientName.value = ingredient.name;
    initStoreDataMap();

    // Populate store data from pivot
    if (ingredient.stores) {
        for (const store of ingredient.stores) {
            if (storeDataMap.value[store.id]) {
                storeDataMap.value[store.id].price = store.pivot?.price ?? '';
                storeDataMap.value[store.id].quantity = store.pivot?.quantity ?? '';
                storeDataMap.value[store.id].product_url = store.pivot?.product_url ?? '';
                storeDataMap.value[store.id].existingImage = store.pivot?.image ?? null;
            }
        }
    }

    // Default to first store that has data, or first store
    const firstStoreWithData = ingredient.stores?.[0]?.id?.toString();
    activeStoreTab.value = firstStoreWithData || props.stores[0]?.id?.toString() || '';
    isDialogOpen.value = true;
};

const submit = async () => {
    if (!ingredientName.value.trim()) {
        toast.error('Please enter an ingredient name.');
        return;
    }

    isSubmitting.value = true;

    const formData = new FormData();
    formData.append('name', ingredientName.value);

    if (editingIngredient.value) {
        formData.append('_method', 'PUT');
    }

    // Add store data
    let storeIndex = 0;
    for (const storeId of Object.keys(storeDataMap.value)) {
        const data = storeDataMap.value[Number(storeId)];
        formData.append(`stores[${storeIndex}][store_id]`, storeId);
        formData.append(`stores[${storeIndex}][price]`, data.price || '');
        formData.append(`stores[${storeIndex}][quantity]`, data.quantity || '');
        formData.append(`stores[${storeIndex}][product_url]`, data.product_url || '');
        if (data.image) {
            formData.append(`stores[${storeIndex}][image]`, data.image);
        }
        storeIndex++;
    }

    const url = editingIngredient.value
        ? admin.ingredients.update.url(editingIngredient.value.id)
        : admin.ingredients.store.url();

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                'Accept': 'text/html, application/xhtml+xml',
                'X-Inertia': 'true',
                'X-Inertia-Version': (document.querySelector('meta[name="inertia-version"]') as HTMLMetaElement)?.content || '',
            },
            body: formData,
        });

        isDialogOpen.value = false;
        router.reload({ only: ['ingredients'] });
        toast.success(editingIngredient.value ? 'Ingredient updated.' : 'Ingredient added.');
    } catch (error) {
        toast.error('An error occurred while saving.');
        console.error(error);
    } finally {
        isSubmitting.value = false;
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
    router.post(admin.ingredients.destroy.url(deleteId.value), { _method: 'DELETE' }, {
        onFinish: () => {
            deleteLoading.value = false;
            deleteId.value = null;
        },
    });
};

const isUpdatingPrices = ref(false);

const updatePrices = async () => {
    isUpdatingPrices.value = true;
    try {
        const response = await fetch(admin.ingredients.updatePrices.url(), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
        });
        const data = await response.json();
        if (response.ok) {
            toast.success(data.message);
            router.reload({ only: ['ingredients'] });
        } else {
            toast.error(data.error || 'Failed to update prices.');
        }
    } catch (error) {
        toast.error('An error occurred while updating prices.');
    } finally {
        isUpdatingPrices.value = false;
    }
};

// Filtering & Pagination
const search = ref('');
const currentPage = ref(1);
const pageSize = 10;

const filteredIngredients = computed(() => {
    return props.ingredients.filter(ingredient => {
        return ingredient.name.toLowerCase().includes(search.value.toLowerCase());
    });
});

const totalPages = computed(() => Math.ceil(filteredIngredients.value.length / pageSize));

const paginatedIngredients = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    const end = start + pageSize;
    return filteredIngredients.value.slice(start, end);
});

watch(search, () => {
    currentPage.value = 1;
});

// Helper: get cheapest price from an ingredient's stores
const getCheapestPrice = (ingredient: any) => {
    if (!ingredient.stores || ingredient.stores.length === 0) return null;
    const prices = ingredient.stores
        .map((s: any) => s.pivot?.price)
        .filter((p: any) => p !== null && p !== undefined);
    if (prices.length === 0) return null;
    return Math.min(...prices);
};

// Helper: get the store with the cheapest price
const getCheapestStore = (ingredient: any) => {
    if (!ingredient.stores || ingredient.stores.length === 0) return null;
    let cheapest: any = null;
    for (const store of ingredient.stores) {
        if (store.pivot?.price != null) {
            if (!cheapest || store.pivot.price < cheapest.pivot.price) {
                cheapest = store;
            }
        }
    }
    return cheapest;
};

// Helper: get first image from stores
const getIngredientImage = (ingredient: any) => {
    if (!ingredient.stores) return null;
    for (const store of ingredient.stores) {
        if (store.pivot?.image) return store.pivot.image;
    }
    return null;
};

// Helper: check if a store tab has any data
const storeHasData = (storeId: number) => {
    const data = storeDataMap.value[storeId];
    if (!data) return false;
    return !!(data.price || data.quantity || data.product_url || data.image || data.existingImage);
};

defineOptions({
    layout: AppLayout,
});
</script>

<template>
    <Head title="Manage Ingredients" />

    <div class="flex flex-col gap-6 p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Ingredients</h1>
                <p class="text-muted-foreground">Manage global list of ingredients.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative w-full sm:w-64">
                    <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        v-model="search"
                        placeholder="Search ingredients..."
                        class="pl-9"
                    />
                </div>
                <Button 
                    @click="updatePrices" 
                    :disabled="isUpdatingPrices" 
                    class="w-full sm:w-auto bg-sky-500 hover:bg-sky-600 text-white border-none shadow-sm transition-all hover:shadow-md"
                >
                    <Wand2 v-if="!isUpdatingPrices" class="mr-2 h-4 w-4" />
                    <Loader2 v-else class="mr-2 h-4 w-4 animate-spin" />
                    Update Prices
                </Button>
                <Button @click="openCreate" class="w-full sm:w-auto">
                    <Plus class="mr-2 h-4 w-4" /> Add Ingredient
                </Button>
            </div>
        </div>

        <div class="rounded-md border bg-card">
            <div class="relative w-full overflow-auto">
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground w-[80px]">Image</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Name</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Stores</th>
                            <th class="h-12 px-4 text-left font-medium text-muted-foreground">Best Price</th>
                            <th class="h-12 px-4 text-right font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ingredient in paginatedIngredients" :key="ingredient.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="p-4">
                                <img v-if="getIngredientImage(ingredient)" :src="`/storage/${getIngredientImage(ingredient)}`" class="h-10 w-10 rounded object-cover" />
                                <div v-else class="h-10 w-10 rounded bg-muted flex items-center justify-center text-[8px]">No Image</div>
                            </td>
                            <td class="p-4 font-medium">{{ ingredient.name }}</td>
                            <td class="p-4">
                                <div v-if="ingredient.stores && ingredient.stores.length > 0" class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="store in ingredient.stores"
                                        :key="store.id"
                                        class="inline-flex items-center gap-1 rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium transition-colors"
                                    >
                                        <img v-if="store.image" :src="`/storage/${store.image}`" class="h-3.5 w-3.5 rounded-full object-cover" />
                                        {{ store.name }}
                                        <span v-if="store.pivot?.price" class="text-muted-foreground ml-0.5">
                                            {{ $page.props.settings.currency_symbol }}{{ store.pivot.price }}
                                        </span>
                                    </span>
                                </div>
                                <span v-else class="text-muted-foreground">-</span>
                            </td>
                            <td class="p-4">
                                <template v-if="getCheapestPrice(ingredient) !== null">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                            {{ $page.props.settings.currency_symbol }}{{ getCheapestPrice(ingredient)?.toFixed(2) }}
                                        </span>
                                        <img
                                            v-if="getCheapestStore(ingredient)?.image"
                                            :src="`/storage/${getCheapestStore(ingredient).image}`"
                                            class="h-4 w-4 rounded-full object-cover"
                                            :title="getCheapestStore(ingredient).name"
                                        />
                                    </div>
                                </template>
                                <span v-else class="text-muted-foreground">-</span>
                            </td>
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
                            <td colspan="5" class="p-8 text-center text-muted-foreground">No ingredients found.</td>
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

        <!-- Add/Edit Dialog with Store Tabs -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{{ editingIngredient ? 'Edit Ingredient' : 'Add Ingredient' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submit" class="space-y-4 py-4">
                    <!-- Ingredient Name (shared across all stores) -->
                    <div class="space-y-2">
                        <Label for="ing-name">Ingredient Name</Label>
                        <Input id="ing-name" v-model="ingredientName" placeholder="e.g. Kipfilet, Melk, Brood" required />
                    </div>

                    <!-- Store Tabs -->
                    <div class="space-y-2" v-if="stores.length > 0">
                        <Label>Store Details</Label>
                        <Tabs v-model="activeStoreTab" class="w-full">
                            <TabsList class="w-full flex h-11 gap-1 bg-muted/60 p-1 rounded-lg">
                                <TabsTrigger
                                    v-for="store in stores"
                                    :key="store.id"
                                    :value="store.id.toString()"
                                    class="flex-1 flex items-center justify-center gap-1.5 relative rounded-md px-3 py-1.5 text-sm font-medium transition-all data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-md data-[state=inactive]:text-muted-foreground data-[state=inactive]:hover:text-foreground data-[state=inactive]:hover:bg-muted"
                                >
                                    <img v-if="store.image" :src="`/storage/${store.image}`" class="h-4 w-4 rounded-full object-cover" />
                                    <span>{{ store.name }}</span>
                                    <span
                                        v-if="storeHasData(store.id)"
                                        class="absolute -top-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-background"
                                    ></span>
                                </TabsTrigger>
                            </TabsList>

                            <TabsContent
                                v-for="store in stores"
                                :key="store.id"
                                :value="store.id.toString()"
                                class="space-y-4 pt-4"
                            >
                                <!-- Product URL with Scrape -->
                                <div class="space-y-2">
                                    <Label :for="`url-${store.id}`">Product URL</Label>
                                    <div class="flex gap-2">
                                        <Input
                                            :id="`url-${store.id}`"
                                            v-model="storeDataMap[store.id].product_url"
                                            placeholder="https://..."
                                            class="flex-1"
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            :disabled="scrapingStoreId !== null || !storeDataMap[store.id].product_url"
                                            @click="scrapeProduct(store.id)"
                                            title="Scrape product data from URL"
                                        >
                                            <Loader2 v-if="scrapingStoreId === store.id" class="h-4 w-4 animate-spin" />
                                            <Wand2 v-else class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>

                                <!-- Quantity -->
                                <div class="space-y-2">
                                    <Label :for="`qty-${store.id}`">Quantity / Unit</Label>
                                    <Input
                                        :id="`qty-${store.id}`"
                                        v-model="storeDataMap[store.id].quantity"
                                        placeholder="e.g. 200g, 1 piece"
                                    />
                                </div>

                                <!-- Price -->
                                <div class="space-y-2">
                                    <Label :for="`price-${store.id}`">Price</Label>
                                    <Input
                                        :id="`price-${store.id}`"
                                        type="number"
                                        step="0.01"
                                        v-model="storeDataMap[store.id].price"
                                    />
                                </div>

                                <!-- Image -->
                                <div class="space-y-2">
                                    <Label>Image</Label>
                                    <div v-if="storeDataMap[store.id].imagePreview || storeDataMap[store.id].existingImage" class="mb-2">
                                        <img
                                            v-if="storeDataMap[store.id].imagePreview"
                                            :src="storeDataMap[store.id].imagePreview!"
                                            class="h-20 w-20 rounded object-cover border"
                                        />
                                        <img
                                            v-else-if="storeDataMap[store.id].existingImage"
                                            :src="`/storage/${storeDataMap[store.id].existingImage}`"
                                            class="h-20 w-20 rounded object-cover border"
                                        />
                                    </div>
                                    <Input type="file" @change="(e: any) => handleImageChange(e, store.id)" />
                                </div>
                            </TabsContent>
                        </Tabs>
                    </div>
                </form>
                <DialogFooter>
                    <Button variant="outline" @click="isDialogOpen = false">Cancel</Button>
                    <Button @click="submit" :disabled="isSubmitting">
                        <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
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
