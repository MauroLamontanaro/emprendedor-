@extends('layouts.app')

@section('content')

<div class="container">
    <h2>Agregar Nuevo Producto</h2>
    <form action="{{ route('productos.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label for="nombre">Nombre del Producto</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre') }}">
            @error('nombre')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="precio">Precio del Producto</label>
            <input type="number" class="form-control" id="precio" name="precio" value="{{ old('precio') }}">
            @error('precio')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="descripcion">Descripcion</label>
            <input type="text" class="form-control" id="descripcion" name="descripcion" value="{{ old('descripcion') }}">
            @error('precio')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        
        <div id="upload-container">
            <div class="carga-producto" >
                <label for="file-input" id="upload-button">
                    <i class="fas fa-camera"></i> Agregar Fotos
                </label>

                <input type="file" name="foto_[]" id="file-input" multiple accept="image/*" onchange="handleFileSelect(event)">
                <div id="preview-container"></div>
                @error('foto_1')
                <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <input type="hidden" name="fotos" id="fotos">
        <input type="hidden" id="latitude" name="latitude">
        <input type="hidden" id="longitude" name="longitude">
        <button type="submit" class="btn btn-primary">Agregar Producto</button>
    </form>
</div>


<script>


function handleFileSelect(event) {
    event.preventDefault();
    const files = event.target.files;
    handleFiles(files);
}

let selectedImages = []; // Lista de todas las imágenes seleccionadas

function handleFiles(files) {
    const previewContainer = document.getElementById('preview-container');
    const existingImages = previewContainer.querySelectorAll('.preview-image');
    const allImages = Array.from(existingImages).map(image => image.src);

    const totalImagesCount = allImages.length + files.length;
    if (totalImagesCount > 4) {
        alert("Solo puedes cargar hasta 4 imágenes.");
        return;
    }

    const photoInput = document.getElementById('fotos');

    for (const file of files) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImageContainer = document.createElement('div');
            previewImageContainer.className = 'preview-image-container';
            const previewImage = document.createElement('img');
            previewImage.src = e.target.result;
            previewImage.className = 'preview-image';
            const deleteButton = document.createElement('button');
            deleteButton.innerHTML = 'X';
            deleteButton.className = 'delete-button';
            deleteButton.addEventListener('click', function() {
                previewContainer.removeChild(previewImageContainer);
                selectedImages = selectedImages.filter(image => image !== e.target.result);
                updatePhotoInputValue();
            });
            previewImageContainer.appendChild(previewImage);
            previewImageContainer.appendChild(deleteButton);
            previewContainer.appendChild(previewImageContainer);
            
            selectedImages.push(e.target.result); // Agregar la nueva imagen a la lista
            console.log(selectedImages); // Verificar las imágenes seleccionadas en la consola
            updatePhotoInputValue(); // Actualizar el valor del campo oculto
        };
        reader.readAsDataURL(file);
    }

    if (totalImagesCount >= 4) {
        document.getElementById('upload-button').style.display = 'none';
    }
}

function updatePhotoInputValue() {
    const photoInputs = document.querySelectorAll('[id^=foto_]'); // Seleccionar todos los campos ocultos con id que comience por 'foto_'
    
    // Iterar sobre los campos ocultos y asignar los valores de las imágenes
    for (let i = 0; i < selectedImages.length && i < photoInputs.length; i++) {
        photoInputs[i].value = selectedImages[i];
    }
    
} console.log(photoInputs);


</script>




<style>
    #upload-container {
        width: 500px;
        text-align: center;
        position: relative;
        /* Agregamos posición relativa para que el botón se posicione correctamente */
    }

    #preview-container {
        width: 285px;
        height: 200px;
        border: black;
        display: flex;
        flex-wrap: wrap;
        margin-top: 1px;
        justify-content: center;
    }

    .preview-image-container {
        position: relative;
    }

    .preview-image {
        width: 140px;
        height: 110px;
        object-fit: cover;
    }

    .delete-button {
        position: absolute;
        top: 5px;
        right: 5px;
        border: none;
        padding: 5px;
        cursor: pointer;
        font-size: 8px;
    }

    .carga-producto {
        border: black;
        width: 280px;
        display: flex;
        height: 223px;
        background-color: black;
        margin-bottom: 100px;
        position: relative;
    }

    #file-input {
        display: none;
    }

    #upload-button {
        color: #fff;
        -webkit-text-stroke: 0.08cap rgb(0, 0, 0); /* Contorno del texto para navegadores WebKit (Chrome, Safari, etc.) */
        font-size: 20px;
        border: none;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 1;
        /* Añadimos un índice z para asegurarnos de que esté por encima de las imágenes cargadas */
    }
</style>

@endsection