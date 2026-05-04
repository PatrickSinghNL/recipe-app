<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppLayout from '@/layouts/AppLayout.vue';
import admin from '@/routes/admin';

const props = defineProps<{
    settings: {
        registration_enabled: boolean;
    };
}>();

const form = useForm({
    registration_enabled: Boolean(props.settings.registration_enabled),
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

        <div class="max-w-2xl">
            <Card>
                <CardHeader>
                    <CardTitle>Registration</CardTitle>
                    <CardDescription>Control how new users can join the platform.</CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
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
                    
                    <div class="flex justify-end pt-4">
                        <Button 
                            @click="submit" 
                            :disabled="form.processing"
                        >
                            <Spinner v-if="form.processing" class="mr-2" />
                            Save Settings
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
