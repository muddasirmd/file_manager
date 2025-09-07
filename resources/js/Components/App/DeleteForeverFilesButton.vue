<template>
    <button @click="onClick" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border 
    border-gray-200 rounded-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 
    focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white
    dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white mr-3">

    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-2">
        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
    </svg>
    
    Delete Forever
    </button>

    <ConfirmationDialog :show="showConfirmationDialog" message="Are you sure you want to delete these files?"
        @cancel="onCancel"
        @confirm="onConfirm">
    </ConfirmationDialog>
</template>

<script setup>
import ConfirmationDialog from '@/Components/ConfirmationDialog.vue';
import { showErrorDialog, showSuccessNotification } from '@/event-bus';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const showConfirmationDialog = ref(false);

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

const emit = defineEmits(['delete']);


function onClick() {
    if(!props.allSelected && !props.selectedIds.length){
        showErrorDialog('Please select files to delete.');
        return
    }
    showConfirmationDialog.value = true;
}

function onCancel(){
    showConfirmationDialog.value = false;
}

function onConfirm() {
    
    if(props.allSelected){
        form.all = true;
        form.ids = [];
    }
    else{
        form.ids = props.selectedIds;
    }

    form.post(route('file.deleteForever'), {
        onSuccess: () => {
            showConfirmationDialog.value = false;
            // Emit an event to notify the parent component that files have been deleted
            emit('delete');
            showSuccessNotification('Selected files have been deleted forever')
        }
    });
}
</script>

<style lang="scss" scoped>

</style>