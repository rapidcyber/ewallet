<?php

namespace App\SystemAdmin;

use App\Models\Merchant;
use Illuminate\Support\Facades\App;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Livewire\Component;

class SystemAdminClients extends Component
{

    public $account_number;

    public $clients;

    protected $rules = [
        'account_number' => 'required|exists:merchants,account_number',
    ];

    public function render()
    {
        $this->clients = Client::where('provider', 'partners')->get();
        return view('system-admin.system-admin-clients')->layout('layouts.system-admin.system-admin');
    }

    public function createClient()
    {
        $validated = $this->validate();
        
        $merchant = Merchant::where([
            'account_number' => $validated['account_number'],
            'status' => 'verified',
        ])->first();

        if (empty($merchant)) {
            $this->addError('account_number', 'Invalid account number');
            return;
        }

        $client_repo = App::make(ClientRepository::class);
        $client_repo->create(
            $merchant->id,
            $merchant->name,
            '',
            'partners',
            false,
            false,
            true,
        );
    }

    public function revoke_client($id)
    {
        $client_repo = App::make(ClientRepository::class);
        $client_repo->delete(Client::find($id));
    }

    public function regenerate_secret($id) {
        $client_repo = App::make(ClientRepository::class);
        $client_repo->regenerateSecret(Client::find($id));
    }
}
