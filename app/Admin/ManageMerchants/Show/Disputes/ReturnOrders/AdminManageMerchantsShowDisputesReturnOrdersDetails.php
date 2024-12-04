<?php

namespace App\Admin\ManageMerchants\Show\Disputes\ReturnOrders;

use App\Models\Employee;
use App\Models\Merchant;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminManageMerchantsShowDisputesReturnOrdersDetails extends Component
{
    public function mount(Merchant $merchant, Employee $employee)
    {

    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('admin.manage-merchants.show.disputes.return-orders.admin-manage-merchants-show-disputes-return-orders-details');
    }
}
