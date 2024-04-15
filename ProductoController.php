<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class ProductoController extends Controller
{
    /**
     * Muestra una lista de todos los productos.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $productos = Producto::all();
        return view('productos.index', compact('productos'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('productos.create');
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    $formData = $request->all();
    // Validar los datos del formulario
    $request->validate([
        'nombre' => 'required',
        'precio' => 'required|numeric',
        'foto_*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Validar todas las fotos
    ]);
    $fotoValues = $request->only(preg_grep('/^foto_/', array_keys($formData)));

    // Imprimir o depurar los valores para verlos
    dd($formData, $fotoValues);
    // Procesar y almacenar las imágenes principales y adicionales
    $imagePaths = [];
    foreach ($request->file('foto_') as $index => $image) {
        $imagePaths[] = $this->storeAndCompressImage($image, $index + 1);
    }

    // Crear una nueva instancia de Producto
    $producto = new Producto();
    $producto->nombre = $request->nombre;
    $producto->precio = $request->precio;
    $producto->descripcion = $request->descripcion;
    $producto->latitude = $request->latitude;
    $producto->longitude = $request->longitude;
    // Agrega aquí la descripción u otros campos si es necesario
    $producto->tienda_id = Auth::user()->tienda->id;

    // Asignar las rutas de las imágenes a las propiedades correspondientes del producto
    foreach ($imagePaths as $index => $imagePath) {
        $fieldName = 'foto_' . ($index + 1);
        $producto->{$fieldName} = $imagePath['filename'];
        if ($request->has($fieldName)) {
            $producto->{$fieldName} = $request->input($fieldName);
        }
    }
   
    // Guardar el producto en la base de datos
    $producto->save();

    // Redireccionar a alguna página después de agregar el producto
    return redirect()->route('productos.index')->with('success', 'Producto agregado correctamente.');
}

private function storeAndCompressImage($image, $index)
{
    // Generar un nuevo nombre de archivo único
    $filename = uniqid() . '.' . $image->getClientOriginalExtension();

    // Comprimir y almacenar la imagen
    $compressedImage = Image::make($image)
        ->resize(500, null, function ($constraint) {
            $constraint->aspectRatio();
        })
        ->encode('jpg', 75);

    // Guardar la imagen en la carpeta deseada (fuera del directorio público)
    $path = 'app/images/' . $filename;
    Storage::put($path, $compressedImage->__toString());

    // Devolver el nombre de archivo y el índice en un arreglo asociativo
    return ['filename' => $filename, 'index' => $index];
}


    /**
     * Muestra el formulario para editar un producto.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function edit(Producto $producto)
    {
        return view('productos.edit', compact('producto'));
    }

    /**
     * Actualiza el producto especificado en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Producto $producto)
    {
        // Validar los datos del formulario
        $request->validate([
            'nombre' => 'required',
            'precio' => 'required|numeric',
            // Agrega las reglas de validación para los otros campos aquí
        ]);

        // Actualizar los datos del producto con los datos del formulario
        $producto->update($request->all());

        // Redireccionar a la página de productos o a donde desees
        return redirect()->route('productos.index')->with('success', '¡El producto ha sido actualizado correctamente!');
    }

    /**
     * Elimina el producto especificado de la base de datos.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\Response
     */
    public function destroy(Producto $producto)
    {
        // Eliminar el producto de la base de datos
        $producto->delete();

        // Redireccionar a la página de productos o a donde desees
        return redirect()->route('productos.index')->with('success', '¡El producto ha sido eliminado correctamente!');
    }

    public function destacar(Request $request)
    {
    
        $productoId = $request->route('producto');
        Producto::where('id', $productoId)->update(['destacado' => true]);
        return redirect()->route('productos.index')->with('success', 'Producto destacado correctamente.');
    }
    
    public function obtenerCantidadProductosDestacados()
{
    $productosDestacados = Producto::where('destacado', true)->count();
    
    return response()->json([
        'success' => true,
        'destacados' => $productosDestacados
    ]);
}

    public function removerDestacado(Request $request, Producto $producto)
    {

        $producto->destacado = false;
        $producto->save();

        return redirect()->route('productos.index')->with('success', 'Producto desdestacado correctamente.');
    }
}
