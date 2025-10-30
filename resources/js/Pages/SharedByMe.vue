<template>
    <AuthenticatedLayout>
       <nav class="flex items-center justify-end p-1 mb-3">
        <div>
            <DownloadFilesButton :all="allSelected" :ids="selectedIds" :shared-by-me="true" class="mr-2" />
        </div> 

       </nav>

       <div class="flex-1 overflow-auto">
        <table class="min-w-full">
            <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left w-[30px] max-w-[30px] pr-0">
                        <Checkbox @change="onSelectAllChange" v-model:checked="allSelected" />
                    </th>                    
                    <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                        Name
                    </th>
                    <th class="text-sm font-medium text-gray-900 px-6 py-4 text-left">
                        Path
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="file, idx of allFiles.data" :key="file.id" class="bg-white border-b transition duration-300 ease-in-out hover:bg-blue-100 cursor-pointer"
                    :class="(selected[file.id] || allSelected) ? 'bg-blue-50' : 'bg-white'"
                    @click="$event => { toggleFileSelection($event, file); shiftKeySelection($event, idx); }"
                    >
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 w-[30px] max-w-[30px] pr-0">
                        <Checkbox v-model="selected[file.id]" :checked="selected[file.id] || allSelected" />
                    </td>                     
                    <td class="flex items-center px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <FileIcon :file="file" />
                        {{ file.name }}
                    </td>                
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ file.path }}
                    </td>                
                </tr>
            </tbody>
        </table>

        <div v-if="!allFiles.data.length" class="py-8 text-center text-sm text-gray-400">
            There is no data in this folder
        </div>

        <div ref="loadMoreIntersect"></div>
       </div>
        
    </AuthenticatedLayout>
</template>


<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { router, Link } from '@inertiajs/vue3'
import FileIcon from '@/Components/App/FileIcon.vue';
import { computed, onMounted, onUpdated, ref } from 'vue';
import { httpGet } from '@/Helper/http-helper';
import Checkbox from '@/Components/Checkbox.vue';
import DownloadFilesButton from '@/Components/App/DownloadFilesButton.vue';

const props = defineProps({
    files: Object,
    folder: Object,
    ancestors: Object,
});

const loadMoreIntersect = ref(null);

const allFiles = ref({
    data: props.files.data,
    next: props.files.links.next
});

const allSelected = ref(false)
const selected = ref({})

const startIndex = ref(0);
const endIndex = ref(0);

/**
 * Below Conversion:
 * 
 * {1: true, 2: false, ...}  => selected.value
 * 
 * [[1, true], [2, false], ...]  => Object.enteries(selected.value)
 * 
 * [[1, true]]  => Object.enteries(selected.value).filter(a => a[1])
 * 
 * [1]  => Object.enteries(selected.value).filter(a => a[1]).map(a => a[0])
 */
const selectedIds = computed(() => Object.entries(selected.value).filter(a => a[1]).map(a => a[0]));


function loadMore(){
    
    if(allFiles.value.next === null) {
        return;
    }

    httpGet(allFiles.value.next).then(res => {
        allFiles.value.data = [...allFiles.value.data, ...res.data];
        allFiles.value.next = res.links.next;
    })
}

function onSelectAllChange(){
    allFiles.value.data.forEach(f => {
        selected.value[f.id] = allSelected.value
    });
}

function toggleFileSelection(event,file){
    
    // if(!event.shiftKey){

        selected.value[file.id] = !selected.value[file.id];
        onSelectCheckboxChange(file)
    // }
}

function shiftKeySelection(event,idx){
   
    if(event.shiftKey){
       
        endIndex.value = idx;

        // if endIndex is less than startIndex, swap them
        if(endIndex.value < startIndex.value){
            [startIndex.value, endIndex.value] = [endIndex.value, startIndex.value];
        }

        // select all files between startIndex and endIndex
        for(let i=startIndex.value; i<= endIndex.value; i++){
            selected.value[allFiles.value.data[i].id] = true
        }

        allFilesSelection();
    }
    else{
        startIndex.value = idx;
    }
}

function onSelectCheckboxChange(file){
    if(!selected.value[file.id]){
        allSelected.value = false;
    }
    else{
        allFilesSelection()
    }
}

function allFilesSelection() {
    let checked = true;
    for(let file of allFiles.value.data){
        if(!selected.value[file.id]){
            checked = false;
            break;
        }
    }
    allSelected.value = checked;
}

function resetForm() {
    // Reset all selections
    allSelected.value = false;
    selected.value = {};
}

// TODO: I think its not being used. Later check it
onUpdated(() => {
    allFiles.value = {
        data: props.files.data,
        next: props.files.links.next
    };
})

onMounted(() => {

    const observer = new IntersectionObserver((entries) => entries.forEach(entry => entry.isIntersecting && loadMore()), {
        rootMargin: '-250px 0px 0px 0px',
    });

    observer.observe(loadMoreIntersect.value)
})
</script>