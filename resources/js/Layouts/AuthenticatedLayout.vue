<template>

    <div class="h-screen bg-gray-50 flex w-full gap-4">
        <Navigation />

        <main @drop.prevent="handleDrop" @dragover.prevent="onDragOver" @dragleave.prevent="onDragLeave"
            class="flex flex-col flex-1 px-4 overflow-hidden" :class="dragOver ? 'dropzone' : ''">

            <template v-if="dragOver" class="text-gray-500 text-center py-8 text-sm">
                Drop files here to upload
            </template>

            <template v-else>
                <div class="flex items-center justify-between w-full">

                    <SearchForm />
                    <UserSettingsDropDown />

                </div>
            
                <div class="flex flex-col flex-1 overflow-hidden">
                    <slot />
                </div>
            </template>
        </main>
    </div>

    <ErrorDialog />
    <FormProgress :form="fileUploadForm" />
</template>


<script setup>
import { ref, onMounted } from 'vue';
import Navigation from '@/Components/App/Navigation.vue';
import SearchForm from '@/Components/App/SearchForm.vue'
import UserSettingsDropDown from '@/Components/App/UserSettingsDropDown.vue'
import { emitter, FILE_UPLOAD_STARTED, showErrorDialog } from '@/event-bus';
import { useForm, usePage } from '@inertiajs/vue3';
import FormProgress from '@/Components/App/FormProgress.vue';
import ErrorDialog from '@/Components/App/ErrorDialog.vue';

const page = usePage();
const fileUploadForm = useForm({
    files: [],
    relative_paths: [],
    parent_id: null,
});

const dragOver = ref(false);

onMounted(() => {
    // This is where you can add any initialization logic if needed
    emitter.on(FILE_UPLOAD_STARTED, uploadFiles)
});



function onDragOver(event) {
    dragOver.value = true;

}

function onDragLeave(event) {
    dragOver.value = false;
}

function handleDrop(event) {

    dragOver.value = false;
    const files = event.dataTransfer.files;

    if (!files.length) {
        return;
    }

    uploadFiles(files);

}

function uploadFiles(files) {

    console.log(files)
    fileUploadForm.parent_id = page.props.folder.id;
    fileUploadForm.files = files;
    fileUploadForm.relative_paths = [...files].map(f => f.webkitRelativePath);

    fileUploadForm.post(route('file.store'),{
        onSuccess: () => {
            
        },
        onError: errors => {
            let message = ""
            if(Object.keys(errors).length > 0) {
                message = errors[Object.keys(errors)[0]]
            }
            else{
                message = "An error occurred while uploading files. Please try again."
            }
            
            showErrorDialog(message);
        },
        onFinish: () => {
            fileUploadForm.clearErrors()
            fileUploadForm.reset()
        }
    })
}
</script>

<style scoped>
    .dropzone {
        width: 100%;
        height: 100%;
        color: #8d8d8d;
        border: 2px dashed gray;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f9f9f9;
        transition: background-color 0.3s ease;
    }
</style>