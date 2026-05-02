<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Search, Moon, Sun, Monitor } from 'lucide-vue-next';
import { useAppearance } from '@/composables/useAppearance';
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { home, login, dashboard } from '@/routes';
import recipes from '@/routes/recipes';

const { appearance, updateAppearance } = useAppearance();

const page = usePage();
const search = ref((page.props.filters as any)?.search || '');

watch(search, (value) => {
    router.get(recipes.index.url({ query: { search: value } }), {}, {
        preserveState: true,
        replace: true,
    });
});
</script>

<template>
    <div class="min-h-screen bg-background text-foreground transition-colors duration-300">
        <!-- Navbar -->
        <header class="sticky top-0 z-40 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="container flex h-16 items-center justify-between">
                <div class="flex items-center gap-6 md:gap-10">
                    <Link :href="home().url" class="flex items-center space-x-2">
                        <AppLogo class="h-8 w-auto" />
                    </Link>
                </div>

                <div class="flex flex-1 items-center justify-end space-x-4">
                    <nav class="flex items-center space-x-2">
                        <div class="relative w-full max-w-sm items-center hidden md:flex">
                            <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                v-model="search"
                                type="search"
                                placeholder="Search recipes..."
                                class="w-[200px] pl-8 lg:w-[300px]"
                            />
                        </div>

                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon">
                                    <Sun class="h-[1.2rem] w-[1.2rem] rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0" />
                                    <Moon class="absolute h-[1.2rem] w-[1.2rem] rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100" />
                                    <span class="sr-only">Toggle theme</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="updateAppearance('light')">
                                    <Sun class="mr-2 h-4 w-4" /> Light
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="updateAppearance('dark')">
                                    <Moon class="mr-2 h-4 w-4" /> Dark
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="updateAppearance('system')">
                                    <Monitor class="mr-2 h-4 w-4" /> System
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <template v-if="!$page.props.auth.user">
                            <Link :href="login().url">
                                <Button variant="ghost">Log in</Button>
                            </Link>
                        </template>
                        <template v-else>
                            <Link :href="dashboard().url">
                                <Button variant="ghost">Dashboard</Button>
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="container py-8">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t py-6 md:py-0">
            <div class="container flex flex-col items-center justify-between gap-4 md:h-24 md:flex-row">
                <p class="text-center text-sm leading-loose text-muted-foreground md:text-left">
                    &copy; 2026 Recipe App. Built with Laravel and Vue.
                </p>
            </div>
        </footer>
    </div>
</template>
