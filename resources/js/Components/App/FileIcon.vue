<template>
    <span class="w-6 h-6 inline-flex items-center justify-center mr-2">
        <svg v-if="file.is_folder" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
        </svg>

        <template v-else>
            <img class="max-w-full" v-if="isImage(file)" :src="'/images/icon/image.png'">
            <img class="max-w-full" v-if="isPDF(file)" :src="'/images/icon/pdf.png'">
            <img class="max-w-full" v-if="isAudio(file)" :src="'/images/icon/audio.png'">
            <img class="max-w-full" v-if="isVideo(file)" :src="'/images/icon/video.png'">
            <img class="max-w-full" v-if="isWord(file)" :src="'/images/icon/word.png'">
            <img class="max-w-full" v-if="isExcel(file)" :src="'/images/icon/excel.png'">
            <img class="max-w-full" v-if="isZip(file)" :src="'/images/icon/zip.png'">
            <img class="max-w-full" v-if="isText(file)" :src="'/images/icon/txt-file.png'">
        </template>

    </span>
</template>

<script setup>
import { isAudio, isPDF ,isExcel, isImage, isText, isVideo, isWord, isZip } from '@/Helper/file-helper';

const {file} = defineProps({
    file: Object,
})
</script>