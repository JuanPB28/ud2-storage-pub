<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HelloWorldController extends Controller
{
    /**
     * Lista todos los ficheros de la carpeta storage/app.
     *
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: Un array con los nombres de los ficheros.
     */
    public function index()
    {
        // Listar los archivos del disco 'local' en la carpeta raíz (storage/app)
        $files = Storage::disk('local')->files();

        // Devolver el JSON con el mensaje y los archivos
        return response()->json([
            'mensaje' => 'Listado de ficheros',
            'contenido' => $files,
        ], 200);
    }

     /**
     * Recibe por parámetro el nombre de fichero y el contenido. Devuelve un JSON con el resultado de la operación.
     * Si el fichero ya existe, devuelve un 409.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @param content Contenido del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function store(Request $request)
    {
        // Validar los parámetros
        try {
            $request->validate([
                'filename' => 'required|string',
                'content' => 'required|string',
            ]);
            $filename = $request->input('filename');
            $content = $request->input('content');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Faltan parámetros: filename y content son obligatorios'
            ], 422);
        }

        // Verificar si el archivo ya existe
        if (Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo ya existe',
            ], 409);
        }

        // Guardar el archivo
        try {
            Storage::disk('local')->put($filename, $content);
            return response()->json([
                'mensaje' => 'Guardado con éxito',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al guardar el archivo',
            ], 500);
        }
    }

     /**
     * Recibe por parámetro el nombre de fichero y devuelve un JSON con su contenido
     *
     * @param name Parámetro con el nombre del fichero.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: El contenido del fichero si se ha leído con éxito.
     */
    public function show(string $filename)
    {
        // Verificar si el archivo existe
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'Archivo no encontrado',
            ], 404);
        }

        // Leer el contenido del archivo
        try {
            $content = Storage::disk('local')->get($filename);
            return response()->json([
                'mensaje' => 'Archivo leído con éxito',
                'contenido' => $content,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al leer el archivo',
            ], 500);
        }
    }

    /**
     * Recibe por parámetro el nombre de fichero, el contenido y actualiza el fichero.
     * Devuelve un JSON con el resultado de la operación.
     * Si el fichero no existe devuelve un 404.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @param content Contenido del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function update(Request $request, string $filename)
    {
        // Validar que el contenido está presente
        try {
            $request->validate([
                'content' => 'required|string',
            ]);
            $content = $request->input('content');

            if (!$filename) {
                return response()->json([
                    'mensaje' => 'Faltan parámetros: filename es obligatorio'
                ], 422);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Faltan parámetros: content es obligatorio'
            ], 422);
        }

        // Verificar si el archivo existe
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo no existe',
            ], 404);
        }

        // Actualizar el contenido del archivo
        try {
            Storage::disk('local')->put($filename, $content);
            return response()->json([
                'mensaje' => 'Actualizado con éxito',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al actualizar el archivo',
            ], 500);
        }
    }

    /**
     * Recibe por parámetro el nombre de ficher y lo elimina.
     * Si el fichero no existe devuelve un 404.
     *
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function destroy(string $filename)
    {
        if (!$filename) {
            return response()->json([
                'mensaje' => 'Faltan parámetros: filename es obligatorio',
            ], 422);
        }

        // Verificar si el archivo existe
        if (!Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El archivo no existe',
            ], 404);
        }

        // Eliminar el archivo
        
        try {
            Storage::disk('local')->delete($filename);
            return response()->json([
                'mensaje' => 'Eliminado con éxito',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar el archivo',
            ], 500);
        }
    }
}
