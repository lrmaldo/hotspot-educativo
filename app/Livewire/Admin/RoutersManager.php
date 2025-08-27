<?php

namespace App\Livewire\Admin;

use App\Models\RouterDevice;
use App\Services\MikrotikHotspotService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class RoutersManager extends Component
{
    public ?int $editingId = null;
    public string $name = '';
    public string $host = '';
    public int $port = 8728;
    public bool $ssl = false;
    public int $timeout = 5;
    public string $username = '';
    public string $password = '';
    public bool $enabled = true;
    public bool $is_default = false;
    public string $notes = '';
    public ?string $testMessage = null;

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:3',
            'host' => 'required|string',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'required|string',
            'password' => 'nullable|string',
            'enabled' => 'boolean',
            'is_default' => 'boolean',
            'notes' => 'nullable|string',
            'ssl' => 'boolean',
            'timeout' => 'required|integer|min:1|max:60'
        ];
    }

    public function edit(int $id): void
    {
        $r = RouterDevice::findOrFail($id);
        $this->editingId = $r->id;
        $this->name = $r->name;
        $this->host = $r->host;
        $this->port = $r->port;
        $this->username = $r->username;
        $this->password = $r->password; // decrypted accessor
        $this->enabled = $r->enabled;
        $this->is_default = $r->is_default;
        $this->notes = $r->notes ?? '';
    $this->ssl = (bool) ($r->ssl ?? false);
    $this->timeout = (int) ($r->timeout ?? 5);
    }

    public function new(): void
    {
        $this->resetForm();
    }

    public function save(): void
    {
        // Reglas dinámicas: password opcional al editar
        $rules = $this->rules();
        if ($this->editingId) {
            $rules['password'] = 'nullable|string';
        }
        $data = $this->validate($rules);

        if ($data['is_default']) {
            RouterDevice::query()->update(['is_default' => false]);
        }

        if ($this->editingId) {
            $router = RouterDevice::findOrFail($this->editingId);
            $router->name = $data['name'];
            $router->host = $data['host'];
            $router->port = $data['port'];
            $router->ssl = $data['ssl'];
            $router->timeout = $data['timeout'];
            $router->username = $data['username'];
            if (!empty($data['password'])) { // Solo si el usuario ingresó nueva contraseña
                $router->password = $data['password']; // Dispara mutator para cifrar
            }
            $router->enabled = $data['enabled'];
            $router->is_default = $data['is_default'];
            $router->notes = $data['notes'] ?? null;
            $router->save(); // Usa mutators
        } else {
            RouterDevice::create($data); // Mutators aplican
        }

        $this->resetForm();
    }

    public function delete(int $id): void
    {
        RouterDevice::whereKey($id)->delete();
        if ($this->editingId === $id) $this->resetForm();
    }

    public function test(int $id): void
    {
        $device = RouterDevice::findOrFail($id);
        $service = new MikrotikHotspotService($device);
        $res = $service->testConnection();
        $this->testMessage = ($res['ok'] ? '✅ ' : '❌ ').$res['message'];
    }

    protected function resetForm(): void
    {
    $this->reset(['editingId','name','host','port','ssl','timeout','username','password','enabled','is_default','notes','testMessage']);
        $this->port = 8728;
        $this->enabled = true;
        $this->is_default = false;
    $this->ssl = false;
    $this->timeout = 5;
    }

    public function render()
    {
        return view('livewire.admin.routers-manager', [
            'routers' => RouterDevice::orderByDesc('is_default')->orderBy('name')->get(),
        ]);
    }
}
