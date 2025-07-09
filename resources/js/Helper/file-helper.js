export function isImage(file){
    return /^image\/\w+$/.test(file.mime)
}

export function isPDF(file){
    return [
        'application/pdf',
        'application/x-pdf',
        'application/acrobat',
        'application/vnd.pdf',
        'text/pdf',
        'text/x-pdf',
    ].includes(file.mime)
}

export function isAudio(file){
    return [
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',
        'audio/x-m4a',
        'audio/webm',
    ].includes(file.mime)
}

export function isVideo(file){
    return [
        'video/mp4',
        'video/mpeg',
        'video/x-matroska',
        'video/webm',
        'video/ogg',
        'video/quicktime',
    ].includes(file.mime)
}

export function isWord(file){
    return [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-word.document.macroEnabled.12',
        'application/vnd.ms-word.template.macroEnabled.12',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
    ].includes(file.mime)
}

export function isExcel(file){
    return [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel.sheet.macroEnabled.12',
        'application/vnd.ms-excel.template.macroEnabled.12',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
    ].includes(file.mime)
}

export function isZip(file){
    return [
        'application/zip',
        'application/x-zip-compressed',
        'application/x-zip',
        'application/x-compress',
        'application/x-compressed',
        'multipart/x-zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
    ].includes(file.mime)
}

export function isText(file){
    return [
        'text/plain',
        'text/csv',
        'text/tab-separated-values',
        'text/tsv',
        'application/json',
        'application/javascript',
        'application/x-javascript',
    ].includes(file.mime)
}