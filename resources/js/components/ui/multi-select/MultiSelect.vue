<script setup lang="ts">
import { ref, computed } from 'vue';
import { Check, X } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { onClickOutside } from '@vueuse/core';

const props = defineProps<{
    modelValue: number[];
    options: { id: number; name: string }[];
    placeholder?: string;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number[]): void;
}>();

const searchQuery = ref('');
const isOpen = ref(false);
const containerRef = ref<HTMLElement | null>(null);

onClickOutside(containerRef, () => {
    isOpen.value = false;
});

const filteredOptions = computed(() => {
    if (!searchQuery.value) return props.options;
    return props.options.filter(option => 
        option.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const selectedOptions = computed(() => {
    return props.options.filter(option => props.modelValue.includes(option.id));
});

const toggleOption = (id: number) => {
    const newValue = [...props.modelValue];
    const index = newValue.indexOf(id);
    if (index > -1) {
        newValue.splice(index, 1);
    } else {
        newValue.push(id);
    }
    emit('update:modelValue', newValue);
};

const removeOption = (id: number) => {
    const newValue = props.modelValue.filter(v => v !== id);
    emit('update:modelValue', newValue);
};
</script>

<template>
    <div class="relative w-full" ref="containerRef">
        <div class="flex flex-wrap gap-2 mb-2" v-if="selectedOptions.length > 0">
            <Badge 
                v-for="option in selectedOptions" 
                :key="option.id" 
                variant="secondary" 
                class="flex items-center gap-1 cursor-pointer hover:bg-destructive hover:text-destructive-foreground transition-colors"
                @click="removeOption(option.id)"
            >
                {{ option.name }}
                <X class="h-3 w-3" />
            </Badge>
        </div>
        
        <Input 
            v-model="searchQuery" 
            :placeholder="placeholder || 'Search...'" 
            @focus="isOpen = true"
        />
        
        <div 
            v-if="isOpen" 
            class="absolute z-50 w-full mt-1 bg-popover text-popover-foreground border rounded-md shadow-md max-h-60 overflow-auto"
        >
            <div 
                v-if="filteredOptions.length > 0"
                v-for="option in filteredOptions" 
                :key="option.id"
                class="px-3 py-2 cursor-pointer hover:bg-muted flex items-center justify-between text-sm transition-colors"
                @click="toggleOption(option.id)"
            >
                <span>{{ option.name }}</span>
                <Check v-if="modelValue.includes(option.id)" class="h-4 w-4 text-primary" />
            </div>
            <div 
                v-else
                class="px-3 py-4 text-sm text-center text-muted-foreground"
            >
                No results found.
            </div>
        </div>
    </div>
</template>
