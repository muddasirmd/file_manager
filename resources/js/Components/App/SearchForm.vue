<template>
    <div class="w-[600px] h-[88px] flex items-center">
        <TextInput type="text"
            class="block w-full mr-2"
            v-model="search"
            @keyup.enter.prevent="onSearch"
            autocomplete
            placeholder="Search for files and folders" />
    </div>
</template>

<script setup>
import TextInput from '@/Components/TextInput.vue';
import { emitter } from '@/event-bus';
import {router, useForm} from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';


const search = ref('')
let params = '';

function onSearch(){
    params.set('search', search.value);
    router.get(window.location.pathname + '?' + params.toString());

    emitter.emit('ON_SEARCH', search.value);
}


onMounted(() => {
    params = new URLSearchParams(window.location.search);
    search.value = params.get('search');
})
</script>

<style scoped>

</style>