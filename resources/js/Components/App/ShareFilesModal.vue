<template>
    <modal :show="props.modelValue" @show="onShow" max-width="sm">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">
                Share Files
            </h2>
            <div class="mt-6">
                <InputLabel for="shareEmail" value="Enter Email Address" class="sr-only"/>
                <TextInput type="text"
                            ref="shareInput"
                            id="shareEmail" v-model="form.email"
                            class="mt-1 block w-full"
                            :class="form.errors.email ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : ''"
                            placeholder="Enter Email Address"
                            @keyup.enter="share"
                />
                <InputError :message="form.errors.email" class="mt-2" />
                
            </div>
            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">Cancel</SecondaryButton>
                <PrimaryButton class="ml-3" 
                                :class="{'opacity-25': form.processing}"
                                @click="share" :disable="form.processing">
                    Submit
                </PrimaryButton>

            </div>
        </div>
    </modal>
</template>

<script setup>
// Imports
import modal from '@/Components/Modal.vue';
import { useForm,usePage } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { nextTick, ref } from 'vue';
import InputError from '@/Components/InputError.vue';
import { showSuccessNotification } from '@/event-bus';

// Props
const props = defineProps({
    modelValue: Boolean,
    allSelected: Boolean,
    selectedIds: Array
});
const emit = defineEmits(['update:modelValue']);

const form = useForm({
    email: null,
    all: false,
    ids: [],
    parent_id: null
});

const page = usePage(); // inertia page object to access props

const shareInput = ref(null);

function share(){
    form.parent_id = page.props.folder.id; // This folder prop coming from the FileController myFiles method method
    
    if(props.allSelected){
        form.all = true;
        form.ids = [];
    }
    else{
        form.all = false;
        form.ids = props.selectedIds;
    }

    const email = form.email;

    form.post(route('file.share'), {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
            form.reset();
            // Show success notification
            showSuccessNotification(`Selected Files have been shared to "${email}" if the emails exists in our system.`);
        },
        onError: () => {
            shareInput.value.focus();
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
    nextTick(() => shareInput.value.focus());
}

</script>