<template>

    <AuthenticatedLayout>
        <pre>{{ files }}</pre>
        
    </AuthenticatedLayout>
</template>


<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const {files} = defineProps({
    files: Array,
});
</script>