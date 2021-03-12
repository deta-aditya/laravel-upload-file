<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <style>
        .image-gallery-item {
            position: relative;
        }

        .image-gallery-item .seq {
            position: absolute;
            right: 10px;
            color: white;
            top: 10px;
            background: green;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .image-gallery-item img {
            width: 250px;
            border-radius: 5px;
        }
    </style>
    <title>Test Upload</title>
</head>
<body>
    <div class="m-5">
        <div id="show-mode-bar">
            <button id="edit-mode-btn" type="button" class="btn btn-primary">Ubah Data</button>
        </div>
        <div id="edit-mode-bar" class="d-none">
            <button id="save-draft-btn" type="button" class="btn btn-primary">Simpan</button>
            <button id="cancel-draft-btn" type="button" class="btn btn-link">Batal</button>
            <div id="file-uploader" class="mt-4">
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <span class="input-group-text" id="inputGroupFileAddon01">Upload</span>
                    </div>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="input-file-upload" aria-describedby="input-file-upload">
                        <label class="custom-file-label" for="input-file-upload">Choose file</label>
                    </div>
                </div>
            </div>
        </div>
        <div id="image-gallery" class="mt-4 d-flex">
            <!-- <div class="image-gallery-item mr-2">
                <div class="seq">1</div>
                <img src="https://images.unsplash.com/photo-1612831198181-fe18661c31b6?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" />
            </div> -->
        </div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script>

$(document).ready(main)

// State
let state = {
    editMode: false,
    links: {
        image_gallery: [
            {
                seq: 1,
                url: "https://images.unsplash.com/photo-1612831198181-fe18661c31b6?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80",
                is_thumbnail: false,
            },
            {
                seq: 2,
                url: "https://images.unsplash.com/photo-1615229998660-29e3cb2232b4?ixid=MXwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHw%3D&ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80",
                is_thumbnail: false,
            },
        ],
        video_link: '',
    },
    uploads: []
}

// Selectors
function combinedImages(state) {
    const combined = [...state.links.image_gallery, ...state.uploads]

    combined.sort((prev, next) => prev.seq - next.seq)

    return combined
}

function nextSeq(state) {
    const links = state.links.image_gallery.map(x => x.seq)
    const uploads = state.uploads.map(x => x.seq)
    const result = [...links, ...uploads].reduce((prev, next) => Math.max(prev, next)) + 1

    return result
}

function composeFormData(state) {
    const formData = new FormData()
    formData.append('links[image_gallery]', JSON.stringify(state.links.image_gallery))
    formData.append('links[video_link]', state.links.video_link)

    const uploads = state.uploads.flatMap(Object.entries)
    uploads.forEach(([key, value]) => formData.append(`uploads[${key}][]`, value))

    return formData
}

// Mutators
const toggleEditMode = mutator((state, _) => ({ 
    ...state, 
    editMode: !state.editMode,
}))

const addUpload = mutator((state, payload) => ({
    ...state,
    uploads: [
        ...state.uploads,
        {
            seq: nextSeq(state),
            file: payload,
            is_thumbnail: false,
        }
    ]
}))

const reorder = mutator((state, payload) => {
    const combined = combinedImages(state)
    const newOrder = payload
        .map(seq => combined.find(item => item.seq == seq))
        .map((item, idx) => ({ ...item, seq: idx + 1 }))

    const uploads = newOrder.filter(item => item.hasOwnProperty('file'))
    const image_gallery = newOrder.filter(item => item.hasOwnProperty('url'))

    return {
        ...state,
        links: {
            ...state.links,
            image_gallery,
        },
        uploads,
    }
})

// Elements & Event Handler
const el = {
    body: $('body'),
    showModeBar: $('#show-mode-bar'),
    editModeButton: $('#edit-mode-btn'),
    editModeBar: $('#edit-mode-bar'),
    saveDraftButton: $('#save-draft-btn'),
    cancelDraftButton: $('#cancel-draft-btn'),
    imageGallery: $('#image-gallery'),
    inputFileUpload: $('#input-file-upload'),
}

function main() {
    render(state, el)

    el.editModeButton.on('click', () => {
        toggleEditMode()
    })

    el.saveDraftButton.on('click', async () => {
        const formData = composeFormData(state)
        const result = await api.modifyGallery(formData)

        el.body.append(result)
    })

    el.cancelDraftButton.on('click', () => {
        toggleEditMode()
    })

    el.inputFileUpload.on('change', (evt) => {
        if (evt.target.files && evt.target.files[0]) {
            addUpload(evt.target.files[0])
        }
    })

    el.imageGallery.sortable({
        animation: 500,
        stop: (event, ui) => {
            const newSeqs = [...event.target.children].map(item => item.dataset.seq)
            reorder(newSeqs)
        }
    })
}

// APIs
const api = {
    async modifyGallery(formData) {
        try {
            const result = await $.ajax({
                url: '/api/attr/villages/current/gallery',
                type: 'POST',
                enctype: 'multipart/form-data',
                data: formData,
                processData: false,
                contentType: false,
                cache: false
            })
            
            return result
        } catch (error) {
            console.error(error)
        }
    }
}

// Renderers
function render(state, el) {
    console.log(state)

    renderBars(state, el)
    renderImageItems(state, el)
}

function renderBars(state, el) {
    if (state.editMode) {
        el.showModeBar.addClass('d-none')
        el.editModeBar.removeClass('d-none')
    } else {
        el.editModeBar.addClass('d-none')
        el.showModeBar.removeClass('d-none')
    }
}

function renderImageItems(state, el) {
    const combined = combinedImages(state)

    el.imageGallery.empty()
    combined.forEach(item => {
        el.imageGallery.append(
            $('<div>').addClass('image-gallery-item mr-2').attr('data-seq', item.seq).append(
                $('<div>').addClass('seq').text(item.seq),
                $('<img>').attr('src', item.url || URL.createObjectURL(item.file))
            )
        )
    })
}

// Utils
function mutator(func) {
    return (payload) => {
        state = func(state, payload)
        render(state, el)
    }
}

</script>
</html>