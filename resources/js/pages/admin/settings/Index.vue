<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import admin from '@/routes/admin';

const props = defineProps<{
    settings: {
        registration_enabled: boolean;
        currency_name: string;
        currency_symbol: string;
    };
}>();

const form = useForm({
    registration_enabled: Boolean(props.settings.registration_enabled),
    currency_name: props.settings.currency_name,
    currency_symbol: props.settings.currency_symbol,
});

const submit = () => {
    form.post(admin.settings.update.url());
};

defineOptions({
    layout: AppLayout,
});
</script>

<template>
    <Head title="System Settings" />

    <div class="flex flex-col gap-6 p-6">
        <div>
            <h1 class="text-2xl font-bold tracking-tight">System Settings</h1>
            <p class="text-muted-foreground">Configure global application behavior.</p>
        </div>

        <div class="max-w-2xl space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Registration</CardTitle>
                    <CardDescription>Control how new users can join the platform.</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex items-center justify-between space-x-2">
                        <div class="flex flex-col space-y-1">
                            <Label for="registration-toggle">Allow Registration</Label>
                            <p class="text-xs text-muted-foreground">
                                If disabled, the registration page will be inaccessible to new visitors.
                            </p>
                        </div>
                        <Checkbox 
                            id="registration-toggle" 
                            v-model:checked="form.registration_enabled"
                            :disabled="form.processing"
                        />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Currency Settings</CardTitle>
                    <CardDescription>Set the global currency used for amounts in the application.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="currency_name">Currency Name</Label>
                        <Input 
                            id="currency_name" 
                            v-model="form.currency_name" 
                            placeholder="e.g. Euro"
                            :disabled="form.processing"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="currency_symbol">Currency Symbol</Label>
                        <Input 
                            id="currency_symbol" 
                            v-model="form.currency_symbol" 
                            placeholder="e.g. €"
                            :disabled="form.processing"
                        />
                    </div>
                </CardContent>
            </Card>

            <div class="flex justify-end pt-4">
                <Button 
                    @click="submit" 
                    :disabled="form.processing"
                >
                    <Spinner v-if="form.processing" class="mr-2" />
                    Save Settings
                </Button>
            </div>
        </div>
    </div>
</template>
