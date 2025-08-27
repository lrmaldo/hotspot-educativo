<?php

namespace App\Livewire\Admin;

use App\Models\Trivia;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class TriviasManager extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';
    public int $perPage = 15;
    public ?int $editingId = null;
    public bool $showForm = false;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;
    public bool $preview = false;
    public $importFile; // UploadedFile
    public bool $showImport = false;
    public bool $importing = false; // estado de progreso
    public ?string $importSummary = null; // resumen final
    public int $totalLines = 0;
    public int $processedLines = 0;
    public int $chunkSize = 100; // líneas por ciclo
    public string $importFilePath = '';
    public array $header = [];
    public array $importRows = [];
    public int $insertedCount = 0;
    public int $updatedCount = 0;
    public int $skippedCount = 0;
    public int $errorCount = 0;
    public bool $autoImport = true; // procesar automáticamente chunks

    // Campos Trivia
    public string $question = '';
    public string $option_a = '';
    public string $option_b = '';
    public string $option_c = '';
    public string $option_d = '';
    public string $correct_option = 'A';
    public bool $active = true;
    public ?string $valid_on = null; // date string

    // No cerrar modal durante la subida de archivo
    protected $keepModal = true;

    protected $queryString = ['search'];

    protected function rules(): array
    {
        return [
            'question' => ['required','string','min:5'],
            'option_a' => ['required','string','min:1'],
            'option_b' => ['required','string','min:1'],
            'option_c' => ['required','string','min:1'],
            'option_d' => ['required','string','min:1'],
            'correct_option' => ['required', Rule::in(['A','B','C','D'])],
            'active' => ['boolean'],
            'valid_on' => ['nullable','date'],
        ];
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function updatedImportFile()
    {
        // No hacer nada cuando se sube el archivo, para mantener el modal abierto
        // La validación se hará cuando el usuario pulse el botón Validar/Iniciar
    }

    protected $listeners = ['upload:finished' => 'handleUploadFinished'];

    public function handleUploadFinished()
    {
        // Este método intercepta el evento de finalización de la subida
        // No hacer nada aquí mantiene el modal abierto
    }

    public function toggleImport(): void
    {
        $this->showImport = ! $this->showImport;
    }

    // Abrir modal de import explícitamente (evita cierres accidentales al reutilizar toggle)
    public function openImport(): void
    {
        $this->showImport = true;
    }

    // Cerrar modal de import
    public function closeImport(): void
    {
        $this->showImport = false;
        $this->importFile = null; // Limpiar archivo al cerrar
        $this->resetImportState(); // Limpiar completamente el estado de importación
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $t = Trivia::findOrFail($id);
        $this->editingId = $t->id;
        $this->question = $t->question;
        $this->option_a = $t->option_a;
        $this->option_b = $t->option_b;
        $this->option_c = $t->option_c;
        $this->option_d = $t->option_d;
        $this->correct_option = $t->correct_option;
        $this->active = $t->active;
        $this->valid_on = optional($t->valid_on)->toDateString();
        $this->showForm = true;
        $this->preview = false;
    }

    public function save(): void
    {
        $data = $this->validate();
        if ($this->editingId) {
            Trivia::whereKey($this->editingId)->update($data);
        } else {
            Trivia::create($data);
        }
        $this->showForm = false;
        $this->resetForm();
        session()->flash('status','Trivia guardada');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmDelete = true;
    }

    public function delete(): void
    {
        if ($this->deleteId) {
            Trivia::whereKey($this->deleteId)->delete();
        }
        $this->confirmDelete = false;
        $this->deleteId = null;
    }

    public function duplicate(int $id): void
    {
        $t = Trivia::findOrFail($id);
        $copy = $t->replicate();
        $copy->question = $t->question.' (Copia)';
        $copy->active = false; // Por seguridad desactivar la copia
        $copy->save();
        session()->flash('status', 'Trivia duplicada (#'.$copy->id.')');
    }

    public function prepareImport(): void
    {
        // Reinicia estado pero conserva el archivo seleccionado
        $this->resetImportState(true);
        // Validación más permisiva para CSV (algunos navegadores usan application/octet-stream)
        $this->validate([
            'importFile' => 'required|file|mimes:csv,txt,text,csv,plain|max:4096'
        ]);

        // Asegurarse de que el directorio existe
        $importDir = storage_path('app'.DIRECTORY_SEPARATOR.'imports');
        if (!is_dir($importDir)) {
            mkdir($importDir, 0755, true);
        }

        // Guardar el archivo directamente para evitar problemas de rutas
        $filename = 'trivia_' . time() . '.csv';
        $this->importFilePath = $importDir . DIRECTORY_SEPARATOR . $filename;

        // Guardar el contenido del archivo directamente
        try {
            $this->importFile->storeAs('imports', $filename);

            if (!file_exists($this->importFilePath)) {
                // Intento alternativo: guardar manualmente
                file_put_contents($this->importFilePath, file_get_contents($this->importFile->getRealPath()));
            }

            if (!file_exists($this->importFilePath)) {
                $this->addError('importFile', 'Error al guardar el archivo después de múltiples intentos.');
                return;
            }
        } catch (\Exception $e) {
            $this->addError('importFile', 'Error al guardar: ' . $e->getMessage());
            return;
        }

        try {
            // Verificar que el archivo se pueda leer
            if (!is_readable($this->importFilePath)) {
                $this->addError('importFile', 'El archivo existe pero no se puede leer. Revisa los permisos.');
                return;
            }

            // Leer el contenido del archivo
            $content = file_get_contents($this->importFilePath);
            if ($content === false) {
                $this->addError('importFile', 'No se pudo leer el contenido del archivo.');
                return;
            }

            // Dividir por líneas
            $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $content));

            if (empty($lines)) {
                $this->addError('importFile', 'El archivo está vacío o no contiene líneas válidas.');
                return;
            }

            // Debug del contenido del archivo (solo en modo debug)
            if (config('app.debug')) {
                $this->addError('importFile', 'Contenido del archivo ('.count($lines).' líneas): '.substr(json_encode($lines), 0, 100).'...');
            }
        } catch (\Exception $e) {
            $this->addError('importFile', 'Error al procesar el archivo: ' . $e->getMessage());
            return;
        }

        foreach ($lines as $index => $raw) {
            if (trim($raw) === '') continue;

            // Elimina los comentarios solo al inicio de la línea
            if (str_starts_with(trim($raw),'#')) {
                continue;
            }

            $row = str_getcsv($raw);

            // Debug de las primeras filas (solo en debug)
            if ($index < 5 && config('app.debug')) {
                $this->addError('importFile', 'Fila '.$index.': '.implode(',', $row));
            }

            if (empty($this->header)) {
                $this->header = array_map(fn($h) => strtolower(trim($h)), $row);
                $this->addError('header', 'Headers detectados: '.implode(',', $this->header));
                continue;
            }

            // Solo agregar filas que no estén vacías
            if (count(array_filter($row)) > 0) {
                $this->importRows[] = $row;
            }
        }
        $this->totalLines = count($this->importRows);
        $this->importing = true; // inicia procesamiento
    }

    public function processChunk(): void
    {
        if (! $this->importing) return;
        if ($this->processedLines >= $this->totalLines) {
            $this->finishImport();
            return;
        }
        $end = min($this->processedLines + $this->chunkSize, $this->totalLines);
        for ($i = $this->processedLines; $i < $end; $i++) {
            $row = $this->importRows[$i];

            // Debugging para mostrar info sobre la fila
            if (count($this->header) !== count($row)) {
                // Si hay desajuste entre headers y columnas
                $this->addError('importFile', 'Fila '.($i+1).': Headers: '.count($this->header).' - Columnas: '.count($row));
                $this->skippedCount++;
                continue;
            }

            $data = array_combine($this->header, $row);
            if ($data === false) {
                $this->skippedCount++;
                continue;
            }

            // Limpiar datos y convertir caracteres especiales si es necesario
            $question = trim($data['question'] ?? '');
            $question = html_entity_decode($question, ENT_QUOTES, 'UTF-8');

            $payload = [
                'question' => $question,
                'option_a' => trim($data['option_a'] ?? ''),
                'option_b' => trim($data['option_b'] ?? ''),
                'option_c' => trim($data['option_c'] ?? ''),
                'option_d' => trim($data['option_d'] ?? ''),
                'correct_option' => strtoupper(trim($data['correct_option'] ?? '')),
                'active' => isset($data['active']) ? (in_array(strtolower($data['active']), ['1','true','yes','si','sí'])) : true,
                'valid_on' => $data['valid_on'] ?? null,
            ];

            // Validación mejorada con mensajes
            if (strlen($payload['question']) < 5) {
                $this->addError('importFile', 'Fila '.($i+1).': Pregunta muy corta (mínimo 5 caracteres)');
                $this->skippedCount++;
                continue;
            }

            if (! in_array($payload['correct_option'], ['A','B','C','D'])) {
                $this->addError('importFile', 'Fila '.($i+1).': Opción correcta inválida: '.$payload['correct_option']);
                $this->skippedCount++;
                continue;
            }

            // Verificar que las opciones no estén vacías
            foreach(['option_a','option_b','option_c','option_d'] as $opt) {
                if (empty($payload[$opt])) {
                    $this->addError('importFile', 'Fila '.($i+1).': Opción '.strtoupper(substr($opt, -1)).' está vacía');
                    $this->skippedCount++;
                    continue 2; // Salta al siguiente ciclo del bucle externo
                }
            }

            try {
                $existing = Trivia::where('question', $payload['question'])->first();
                if ($existing) {
                    $existing->update($payload);
                    $this->updatedCount++;
                } else {
                    Trivia::create($payload);
                    $this->insertedCount++;
                }
            } catch (\Throwable $e) {
                $this->addError('importFile', 'Error en fila '.($i+1).': '.$e->getMessage());
                report($e);
                $this->errorCount++;
            }
        }
        $this->processedLines = $end;
        if ($this->processedLines >= $this->totalLines) {
            $this->finishImport();
        }
    }

    protected function finishImport(): void
    {
        $this->importing = false;
    $this->autoImport = false;
        $summary = "Importación: $this->insertedCount nuevas, $this->updatedCount actualizadas, $this->skippedCount saltadas, $this->errorCount con error";
        $this->importSummary = $summary;
        session()->flash('status', $summary);
        $this->importFile = null;
        // Mantener modal abierto para mostrar resumen; se podría cerrar automáticamente.
    }

    public function resetImportState(bool $keepFile = false): void
    {
        $file = $keepFile ? $this->importFile : null;
        $this->reset([
            'importSummary','totalLines','processedLines','importFilePath','header','importRows',
            'insertedCount','updatedCount','skippedCount','errorCount'
        ]);
        $this->importing = false;
        $this->autoImport = true;
        $this->importFile = $file; // Solo se conserva si $keepFile = true
    }

    public function pauseAuto(): void { $this->autoImport = false; }
    public function resumeAuto(): void { if($this->importing) $this->autoImport = true; }

    public function prepareNewImport(): void
    {
        $this->resetImportState();
        // Eliminar el resumen previo para mostrar formulario de carga
        $this->importSummary = null;
    }

    public function downloadTemplate()
    {
        $content = "question,option_a,option_b,option_c,option_d,correct_option,active,valid_on\n";
        $content .= "Capital de Francia?,Madrid,París,Roma,Berlín,B,1,\n";
        $content .= "Resultado de 3+5?,6,7,8,9,C,1,\n";
        $content .= "¿Color del cielo despejado?,Rojo,Verde,Azul,Amarillo,C,1,\n";

        return response()->streamDownload(function() use ($content) { echo $content; }, 'trivias_template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8'
        ]);
    }

    public function exportCsv(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fileName = 'trivias_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ];
        $callback = function() {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','question','option_a','option_b','option_c','option_d','correct_option','active','valid_on']);
            Trivia::orderBy('id')->chunk(200, function($chunk) use ($out){
                foreach ($chunk as $t) {
                    fputcsv($out, [
                        $t->id,
                        $t->question,
                        $t->option_a,
                        $t->option_b,
                        $t->option_c,
                        $t->option_d,
                        $t->correct_option,
                        $t->active ? 1 : 0,
                        optional($t->valid_on)->toDateString(),
                    ]);
                }
            });
            fclose($out);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->preview = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId','question','option_a','option_b','option_c','option_d','correct_option','active','valid_on']);
        $this->correct_option = 'A';
        $this->active = true;
        $this->valid_on = null;
    }

    public function togglePreview(): void
    {
        $this->preview = ! $this->preview;
    }

    public function getPreviewTriviaProperty(): array
    {
        return [
            'question' => $this->question,
            'options' => [
                'A' => $this->option_a,
                'B' => $this->option_b,
                'C' => $this->option_c,
                'D' => $this->option_d,
            ],
            'correct' => $this->correct_option,
        ];
    }

    public function render()
    {
        $list = Trivia::query()
            ->when($this->search, function($q){
                $s = '%'.$this->search.'%';
                $q->where('question','like',$s);
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.admin.trivias-manager', [
            'trivias' => $list,
        ]);
    }
}
