<template>
    <modal :show="modelValue" @show="onShow" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">
                Create New Folder
            </h2>
            <div class="mt-6">
                <InputLabel for="folderName" value="Folder Name" class="sr-only"/>
                <TextInput type="text"
                            ref="folderNameInput"
                            id="folderName" v-model="form.name"
                            class="mt-1 block w-full"
                            :class="form.errors.name ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                            placeholder="Folder name"
                            @keyup.enter="createFolder"
                />
                <InputError :message="form.errors.name" class="mt-2" />
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton class="ml-3" 
                                :class="{'opacity-25': form.processing}"
                                @click="createFolder" :disable="form.processing">
                    Submit
                </PrimaryButton>

            </div>
        </div>
    </modal>
</template>

<script setup>
// Imports
import modal from '@/Components/Modal.vue';
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { nextTick, ref } from 'vue';


// Props
const {modelValue} = defineProps({
    modelValue: Boolean
});
const emit = defineEmits(['update:modelValue']);

const form = useForm({
    name: '',
});

const folderNameInput = ref(null);

function createFolder(){
    form.post(route('folder.create'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
            form.reset();
            // Show success notification
        },
        onError: () => {
            folderNameInput.value.focus();
        },
    })
}

function closeModal(){
    emit('update:modelValue');
    form.clearErrors();
    form.reset();
}

function onShow(){
    // Focus the input when the modal is shown
    // This is to ensure that the input is focused after the modal is shown
    nextTick(() => folderNameInput.value.focus());
}

</script>