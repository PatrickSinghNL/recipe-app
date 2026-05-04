<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { CheckCircle, XCircle, Shield, Trash2, User } from 'lucide-vue-next';
import { ref } from 'vue';
import DeleteConfirmModal from '@/components/DeleteConfirmModal.vue';
import admin from '@/routes/admin';

defineProps<{
    users: any[];
}>();

const approveUser = (id: number) => {
    router.post(admin.users.approve.url(id));
};

const toggleAdmin = (id: number) => {
    router.post(admin.users.toggleAdmin.url(id));
};

const deleteId = ref<number | null>(null);
const deleteLoading = ref(false);

const confirmDelete = (id: number) => {
    deleteId.value = id;
};

const handleDelete = () => {
    if (!deleteId.value) return;
    deleteLoading.value = true;
    router.delete(admin.users.destroy.url(deleteId.value), {
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
    <Head title="User Management" />

    <div class="flex flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">User Management</h1>
            <p class="text-muted-foreground">Approve new registrations and manage user roles.</p>
        </div>

        <div class="rounded-md border bg-card">
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Name</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Email</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Status</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Role</th>
                            <th class="h-12 px-4 text-right align-middle font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        <tr v-for="user in users" :key="user.id" class="border-b transition-colors hover:bg-muted/50">
                            <td class="p-4 align-middle font-medium">{{ user.name }}</td>
                            <td class="p-4 align-middle text-muted-foreground">{{ user.email }}</td>
                            <td class="p-4 align-middle">
                                <Badge :variant="user.is_approved ? 'default' : 'secondary'">
                                    {{ user.is_approved ? 'Approved' : 'Pending' }}
                                </Badge>
                            </td>
                            <td class="p-4 align-middle">
                                <Badge :variant="user.is_admin ? 'destructive' : 'outline'">
                                    {{ user.is_admin ? 'Admin' : 'User' }}
                                </Badge>
                            </td>
                            <td class="p-4 align-middle text-right">
                                <div class="flex justify-end gap-2">
                                    <Button 
                                        v-if="!user.is_approved" 
                                        variant="outline" 
                                        size="sm" 
                                        @click="approveUser(user.id)"
                                    >
                                        <CheckCircle class="mr-2 h-4 w-4" /> Approve
                                    </Button>
                                    <Button 
                                        variant="ghost" 
                                        size="sm" 
                                        @click="toggleAdmin(user.id)"
                                        :disabled="user.id === $page.props.auth.user.id"
                                    >
                                        <Shield class="mr-2 h-4 w-4" /> {{ user.is_admin ? 'Demote' : 'Make Admin' }}
                                    </Button>
                                    <Button 
                                        variant="ghost" 
                                        size="sm" 
                                        @click="confirmDelete(user.id)"
                                        :disabled="user.id === $page.props.auth.user.id"
                                    >
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <DeleteConfirmModal
        :open="deleteId !== null"
        title="Delete User"
        description="Are you sure you want to permanently delete this user account? This action cannot be undone."
        :loading="deleteLoading"
        @update:open="(v) => { if (!v) deleteId = null; }"
        @confirm="handleDelete"
    />
</template>
