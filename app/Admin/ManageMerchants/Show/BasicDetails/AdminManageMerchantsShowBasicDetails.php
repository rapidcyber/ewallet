<?php

namespace App\Admin\ManageMerchants\Show\BasicDetails;

use App\Models\Merchant;
use App\Traits\WithImage;
use App\Traits\WithImageUploading;
use App\Traits\WithValidPhoneNumber;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class AdminManageMerchantsShowBasicDetails extends Component
{
    use WithImage, WithValidPhoneNumber;

    public Merchant $merchant;
    
    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant->load(['details', 'media', 'category']);
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('admin.manage-merchants.show.basic-details.admin-manage-merchants-show-basic-details');
    }
}
