<template>
    
    <div class="h-screen bg-gray-50 flex w-full gap-4">
        <Navigation />

        <main class="flex flex-col flex-1 px-4 overflow-hidden">
            <div class="flex items-center justify-between w-full">

                <SearchForm />
                <UserSettingsDropDown />
            </div>
            <div class="flex flex-col flex-1 overflow-hidden">
                <slot />
            </div>

        </main>
    </div>
</template>


<script setup>
import { ref } from 'vue';
import Navigation from '@/Components/App/Navigation.vue';
import { Link } from '@inertiajs/vue3';
import SearchForm from '@/Components/App/SearchForm.vue'
import UserSettingsDropDown from '@/Components/App/UserSettingsDropDown.vue'

const showingNavigationDropdown = ref(false);
</script>
