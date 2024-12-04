<?php

namespace App\SystemAdmin;

use App\Models\AppAccess;
use Livewire\Attributes\Validate;
use Livewire\Component;

class SystemAdminAppAccess extends Component
{

    public $accesses;

    #[Validate('required')]
    public $passkey = '';
    #[Validate('required|unique:app_accesses,name')]
    public $name = '';

    public function render()
    {
        $this->accesses = AppAccess::all();
        return view('system-admin.system-admin-app-access')->layout('layouts.system-admin.system-admin');
    }


    public function generate_key()
    {
        $this->validate();
        $hash = hash('sha256', $this->passkey);

        $access = AppAccess::firstOrNew([
            'access' => $hash,
        ]);
        $access->name = $this->name;
        $access->save();

        $this->passkey = '';
        $this->name = '';
    }

    public function delete(int $id)
    {
        AppAccess::find($id)->first()->delete();
    }
}
