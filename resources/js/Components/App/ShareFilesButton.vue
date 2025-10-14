<template>
    <button @click="onClick" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border 
    border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 
    focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white
    dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white mr-3">

    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 mr-2">
        <path fill-rule="evenodd"
                d="M15.75 4.5a3 3 0 11.825 2.066l-8.421 4.679a3.002 3.002 0 010 1.51l8.421 4.679a3 3 0 11-.729 1.31l-8.421-4.678a3 3 0 110-4.132l8.421-4.679a3 3 0 01-.096-.755z"
                clip-rule="evenodd"/>
    </svg>
    
    Share
    </button>

    <ShareFilesModal v-model="showEmailModal" />
</template>

<script setup>

import { showErrorDialog, showSuccessNotification } from '@/event-bus';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import ShareFilesModal from './ShareFilesModal.vue';

const showEmailModal = ref(false);

const page = usePage();
const form = useForm({
    all: null,
    ids: [],
    parentId: null
});

const props = defineProps({
    allSelected:{
        type: Boolean,
        default: false,
        required: false
    },
    selectedIds: {
        type: Array,
        required: false,
    }
});

const emit = defineEmits(['restore']);


function onClick() {
    if(!props.allSelected && !props.selectedIds.length){
        showErrorDialog('Please select files to share.');
        return
    }
    showEmailModal.value = true;
}

function onCancel(){
    showEmailModal.value = false;
}

function onConfirm() {
    
    if(props.allSelected){
        form.all = true;
    }
    else{
        form.ids = props.selectedIds;
    }

    form.post(route('file.restore'), {
        onSuccess: () => {
            showEmailModal.value = false;
            // Emit an event to notify the parent component that files have been restored
            emit('restore');
            showSuccessNotification('Selected files have been restored')
        }
    });
}
</script>

<style lang="scss" scoped>

</style>