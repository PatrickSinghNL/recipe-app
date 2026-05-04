<script setup lang="ts">
import { Trash2, AlertTriangle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';

defineProps<{
    open: boolean;
    title?: string;
    description?: string;
    loading?: boolean;
}>();

const emit = defineEmits<{
    'update:open': [value: boolean];
    confirm: [];
}>();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent class="sm:max-w-[420px]">
            <DialogHeader>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-destructive/10">
                        <AlertTriangle class="h-5 w-5 text-destructive" />
                    </div>
                    <div>
                        <DialogTitle class="text-base">
                            {{ title ?? 'Delete item' }}
                        </DialogTitle>
                        <DialogDescription class="mt-0.5 text-sm">
                            {{ description ?? 'This action cannot be undone.' }}
                        </DialogDescription>
                    </div>
                </div>
            </DialogHeader>

            <DialogFooter class="mt-2 gap-2 sm:gap-0">
                <Button
                    variant="outline"
                    :disabled="loading"
                    @click="emit('update:open', false)"
                >
                    Cancel
                </Button>
                <Button
                class="ml-2"
                    variant="destructive"
                    :disabled="loading"
                    @click="emit('confirm')"
                >
                    <Trash2 class="mr-2 h-4 w-4" />
                    {{ loading ? 'Deleting…' : 'Delete' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
