<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Clock } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';

import { logout as logoutRoute } from '@/routes';

defineOptions({
    layout: AuthLayout,
});

const logout = () => {
    router.post(logoutRoute().url);
};
</script>

<template>
    <Head title="Pending Approval" />

    <div class="flex flex-col items-center text-center space-y-6">
        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-primary/10">
            <Clock class="h-10 w-10 text-primary animate-pulse" />
        </div>
        
        <div class="space-y-2">
            <h1 class="text-2xl font-bold tracking-tight">Account Pending Approval</h1>
            <p class="text-muted-foreground leading-relaxed">
                Thank you for registering! Your account is currently awaiting review by an administrator.
                You will be able to access the full application once your account has been approved.
            </p>
        </div>

        <div class="w-full pt-4">
            <Button variant="outline" class="w-full" @click="logout">
                Log Out
            </Button>
        </div>
    </div>
</template>
