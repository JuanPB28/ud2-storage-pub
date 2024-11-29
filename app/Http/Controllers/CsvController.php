<?php

namespace App\Http\Controllers;

use App\Utils\CsvUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class CsvController extends Controller
{
    /**
     * Lista todos los ficheros CSV de la carpeta storage/app.
     *
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: Un array con los nombres de los ficheros.
     */
    public function index()
    {
        try {
            // Obtener todos los archivos en storage/app
            $files = Storage::disk('local')->allFiles();
            $validCsvFiles = [];

            // Iterar sobre los archivos y validar si son CSV
            foreach ($files as $file) {
                if (!pathinfo($file, PATHINFO_EXTENSION) === 'csv') {
                    continue;
                }

                // Comprobar si es un CSV válido
                $content = Storage::disk('local')->get($file);
                if (CsvUtils::decode($content) === false) {
                    continue;
                }

                $validCsvFiles[] = basename($file);
            }

            // Devolver la respuesta en formato JSON
            return response()->json([
                'mensaje' => 'Operación exitosa',
                'contenido' => $validCsvFiles,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al leer los archivos',
            ], 500);
        }
    }

   /**
     * Recibe por parámetro el nombre de fichero y el contenido CSV y crea un nuevo fichero con ese nombre y contenido en storage/app. 
     * Devuelve un JSON con el resultado de la operación.
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

        //Validar si el contenido es un CSV válido
        if (CsvUtils::decode($content) === false) {
            return response()->json(['mensaje' => 'Contenido no es un CSV válido'], 415);
        }

        // Verificar si el archivo ya existe
        if (Storage::disk('local')->exists($filename)) {
            return response()->json([
                'mensaje' => 'El fichero ya existe',
            ], 409);
        }

        // Guardar el archivo
        try {
            Storage::disk('local')->put($filename, $content);
            return response()->json([
                'mensaje' => 'Fichero guardado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al guardar el archivo',
            ], 500);
        }
    }

    /**
     * Recibe por parámetro el nombre de un fichero CSV el nombre de fichero y devuelve un JSON con su contenido.
     * Si el fichero no existe devuelve un 404.
     * Hay que hacer uso lo visto en la presentación CSV to JSON.
     *
     * @param name Parámetro con el nombre del fichero CSV.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     * - contenido: El contenido del fichero si se ha leído con éxito.
     */
    public function show(string $id)
    {
        if (!$id) {
            return response()->json([
                'mensaje' => 'Faltan parámetros: filename es obligatorio',
            ], 422);
        }

        // Verificar si el archivo existe
        if (!Storage::disk('local')->exists($id)) {
            return response()->json([
                'mensaje' => 'El fichero no existe',
            ], 404);
        }

        // Leer el contenido del archivo
        try {
            $content = CsvUtils::decode(Storage::disk('local')->get($id));

            return response()->json([
                'mensaje' => 'Fichero leído con éxito',
                'contenido' => $content,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al leer el archivo',
            ], 500);
        }
    }

   /**
     * Recibe por parámetro el nombre de fichero, el contenido CSV y actualiza el fichero CSV. 
     * Devuelve un JSON con el resultado de la operación.
     * Si el fichero no existe devuelve un 404.
     * Si el contenido no es un JSON válido, devuelve un 415.
     * 
     * @param filename Parámetro con el nombre del fichero. Devuelve 422 si no hay parámetro.
     * @param content Contenido del fichero. Devuelve 422 si no hay parámetro.
     * @return JsonResponse La respuesta en formato JSON.
     *
     * El JSON devuelto debe tener las siguientes claves:
     * - mensaje: Un mensaje indicando el resultado de la operación.
     */
    public function update(Request $request, string $id)
    {
        // Validar que el contenido está presente
        try {
            $request->validate([
                'content' => 'required|string',
            ]);
            $content = $request->input('content');

            if (!$id) {
                return response()->json([
                    'mensaje' => 'Faltan parámetros: filename es obligatorio'
                ], 422);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'mensaje' => 'Faltan parámetros: content es obligatorio'
            ], 422);
        }

        //Validar si el contenido es un CSV válido
        if (CsvUtils::decode($content) === false) {
            return response()->json(['mensaje' => 'Contenido no es un CSV válido'], 415);
        }

        // Verificar si el archivo existe
        if (!Storage::disk('local')->exists($id)) {
            return response()->json([
                'mensaje' => 'El fichero no existe',
            ], 404);
        }

        // Actualizar el contenido del archivo
        try {
            Storage::disk('local')->put($id, $content);
            return response()->json([
                'mensaje' => 'Fichero actualizado exitosamente',
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
    public function destroy(string $id)
    {
        if (!$id) {
            return response()->json([
                'mensaje' => 'Faltan parámetros: filename es obligatorio',
            ], 422);
        }

        // Verificar si el archivo existe
        if (!Storage::disk('local')->exists($id)) {
            return response()->json([
                'mensaje' => 'El fichero no existe',
            ], 404);
        }

        // Eliminar el archivo
        try {
            Storage::disk('local')->delete($id);
            return response()->json([
                'mensaje' => 'Fichero eliminado exitosamente',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'mensaje' => 'Error al eliminar el archivo',
            ], 500);
        }
    }
}