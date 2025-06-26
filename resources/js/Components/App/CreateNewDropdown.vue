<template>
 <Menu as="div" class="relative block text-left">
        <div>
            <MenuButton
                class="flex w-full justify-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Create New
                
            </MenuButton>
        </div>

        <transition enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0">
            <MenuItems
                class="absolute left-0 mt-2 w-32 origin-top-right divide-y divide-gray-100 rounded-md bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                
                <div class="px-1 py-1">
                    <MenuItem v-slot="{ active }">
                        <a href="#" @click.prevent="showCreateFolderModel" class="text-gray-700 block px-4 py-2 text-sm">New Folder</a>
                    </MenuItem>
                </div>

                <div class="px-1 py-1">
                    <FileUploadMenuItem />

                    <FolderUploadMenuItem />

                </div>

            </MenuItems>
        </transition>
    </Menu>

    <CreateFolderModel v-model="createFolderModel" />
</template>

<script setup>
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import CreateFolderModel from '@/Components/App/CreateFolderModel.vue';
import { ref } from 'vue'; 
import FileUploadMenuItem from '@/Components/App/FileUploadMenuItem.vue';
import FolderUploadMenuItem from '@/Components/App/FolderUploadMenuItem.vue';

const createFolderModel = ref(false);


function showCreateFolderModel() {
    createFolderModel.value = true;
}
</script>

<style lang="scss" scoped>

</style>