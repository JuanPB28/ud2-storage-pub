<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

use App\Utils\CsvUtils;

class CsvTest extends TestCase
{
    public function test_index_returns_valid_csv_files()
    {
        Storage::fake('local');

        Storage::disk('local')->put('valid.csv', CsvUtils::encode([['key1' => 'value1', 'key2' => 'value2'], ['key1' => 'value3', 'key2' => 'value4'],]));
        Storage::disk('local')->put('invalid.txt', 'This is not a CSV file');

        $response = $this->getJson('/api/csv');

        $response->assertStatus(200)
                 ->assertJson([
                     'mensaje' => 'Operación exitosa',
                     'contenido' => ['valid.csv']
                 ]);
    }

    public function test_store_creates_new_csv_file()
    {
        Storage::fake('local');

        $data = [
            'filename' => 'newfile.csv',
            'content' => CsvUtils::encode([['key' => 'value'],])
        ];

        $response = $this->postJson('/api/csv', $data);

        $response->assertStatus(200)
                 ->assertJson(['mensaje' => 'Fichero guardado exitosamente']);

        Storage::assertExists('newfile.csv');
    }

    public function test_store_returns_409_if_file_exists()
    {
        Storage::fake('local');

        Storage::disk('local')->put('existingfile.csv', CsvUtils::encode([['key' => 'value'],]));

        $data = [
            'filename' => 'existingfile.csv',
            'content' => CsvUtils::encode([['key' => 'value'],])
        ];

        $response = $this->postJson('/api/csv', $data);

        $response->assertStatus(409)
                 ->assertJson(['mensaje' => 'El fichero ya existe']);
    }

    public function test_store_returns_415_if_content_is_invalid_csv()
    {
        Storage::fake('local');

        $data = [
            'filename' => 'invalidfile.csv',
            'content' => 'This is not a CSV'
        ];

        $response = $this->postJson('/api/csv', $data);

        $response->assertStatus(415)
                 ->assertJson(['mensaje' => 'Contenido no es un CSV válido']);
    }

    public function test_show_returns_file_content()
    {
        Storage::fake('local');

        Storage::disk('local')->put('existingfile.csv', CsvUtils::encode([['key' => 'value'],]));

        $response = $this->getJson('/api/csv/existingfile.csv');

        $response->assertStatus(200)
                    ->assertJson([
                        'mensaje' => 'Fichero leído con éxito',
                        'contenido' => CsvUtils::decode(Storage::disk('local')->get('existingfile.csv'))
                    ]);
    }

    public function test_show_returns_404_if_file_not_exists()
    {
        Storage::fake('local');

        $response = $this->getJson('/api/csv/nonexistentfile.csv');

        $response->assertStatus(404)
                 ->assertJson(['mensaje' => 'El fichero no existe']);
    }

    public function test_update_modifies_existing_file()
    {
        Storage::fake('local');

        Storage::disk('local')->put('existingfile.csv', CsvUtils::encode([['key' => 'value'],]));

        $data = [
            'filename' => 'existingfile.csv',
            'content' => CsvUtils::encode([['new_key' => 'new_value'],])
        ];

        $response = $this->putJson('/api/csv/existingfile.csv', $data);

        $response->assertStatus(200)
                 ->assertJson(['mensaje' => 'Fichero actualizado exitosamente']);

        Storage::assertExists('existingfile.csv');
        $this->assertEquals(CsvUtils::encode([['new_key' => 'new_value'],]), Storage::disk('local')->get('existingfile.csv'));
    }

    public function test_update_returns_404_if_file_not_exists()
    {
        Storage::fake('local');

        $data = [
            'filename' => 'nonexistentfile.csv',
            'content' => CsvUtils::encode([['key' => 'value'],])
        ];

        $response = $this->putJson('/api/csv/nonexistentfile.csv', $data);

        $response->assertStatus(404)
                 ->assertJson(['mensaje' => 'El fichero no existe']);
    }

    public function test_update_returns_415_if_content_is_invalid_csv()
    {
        Storage::fake('local');

        Storage::disk('local')->put('existingfile.csv', CsvUtils::encode([['key' => 'value'],]));

        $data = [
            'filename' => 'existingfile.csv',
            'content' => 'This is not a CSV'
        ];

        $response = $this->put('/api/csv/existingfile.csv', $data);

        $response->assertStatus(415)
                 ->assertJson(['mensaje' => 'Contenido no es un CSV válido']);
    }

    public function test_destroy_deletes_existing_file()
    {
        Storage::fake('local');

        Storage::disk('local')->put('existingfile.csv', CsvUtils::encode([['key' => 'value'],]));

        $response = $this->deleteJson('/api/csv/existingfile.csv');

        $response->assertStatus(200)
                 ->assertJson(['mensaje' => 'Fichero eliminado exitosamente']);

        Storage::assertMissing('existingfile.csv');
    }

    public function test_destroy_returns_404_if_file_not_exists()
    {
        Storage::fake('local');

        $response = $this->deleteJson('/api/csv/nonexistentfile.csv');

        $response->assertStatus(404)
                 ->assertJson(['mensaje' => 'El fichero no existe']);
    }
}