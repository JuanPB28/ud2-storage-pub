<?php

namespace App\Utils;

use Exception;

class CsvUtils {
    public static function encode(mixed $data): string|false {
        try {
            if (empty($data)) {
                return '';
            }

            $formatted_data = '';
        
            // Primera fila del CSV como nombre de las claves
            $keys = array_keys($data[0]);
            $formatted_data = implode(';', $keys) . "\n";
        
            // Recorrer el array y convertir los valores en CSV
            $elementoCount = count($data);
            for ($i = 0; $i < $elementoCount; $i++) {
                $formatted_data .= implode(';', $data[$i]);
                if ($i < $elementoCount - 1) {
                    $formatted_data .= "\n";
                }
            }
        
            return $formatted_data;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function decode(string $csv): mixed {
        try {
            if (empty(trim($csv))){
                throw new Exception('Error: CSV vacío.');
            }

            // Dividir el CSV en filas 
            $lines = explode("\n", trim($csv)); 

            if (count($lines) < 2) {
                throw new Exception('Format Error: Valores insuficientes');
            }

            // Array de claves
            $keys = explode(';', array_shift($lines));

            if (count($keys) < 1) {
                throw new Exception('Format Error: Claves insuficientes');
            }

            $data = [];

            // Recorrer cada línea y convertirla en un array asociativo
            foreach ($lines as $line) {
                if (!empty($line)) {
                    $values = explode(';', $line);
                    $data[] = array_combine($keys, $values);
                }
            }

            return $data;
        } catch (\Exception $e) {
            return false;
        }
    }
}