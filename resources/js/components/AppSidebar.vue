<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { BookOpen, LayoutGrid, ExternalLink, Users, Settings } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, home } from '@/routes';
import admin from '@/routes/admin';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
    },
    {
        title: 'Recipes',
        href: admin.recipes.index.url(),
        icon: BookOpen,
    },
    {
        title: 'Categories',
        href: admin.categories.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Ingredients',
        href: admin.ingredients.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Supplies',
        href: admin.supplies.index.url(),
        icon: LayoutGrid,
    },
    {
        title: 'Settings',
        href: admin.settings.index.url(),
        icon: Settings,
        items: [
            {
                title: 'System Settings',
                href: admin.settings.index.url(),
            },
            {
                title: 'User Management',
                href: admin.users.index.url(),
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'View Frontend',
        href: home().url,
        icon: ExternalLink,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
