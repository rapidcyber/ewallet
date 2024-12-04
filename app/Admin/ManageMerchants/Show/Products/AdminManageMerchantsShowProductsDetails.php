<?php

namespace App\Admin\ManageMerchants\Show\Products;

use App\Models\AdminLog;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\Product;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithValidPhoneNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AdminManageMerchantsShowProductsDetails extends Component
{
    use WithImage, WithValidPhoneNumber;

    public Product $product;
    public $visible = false;
    public $actionType = '';

    public function mount(Merchant $merchant, Product $product)
    {
        $this->product = Product::with(['merchant.category', 'category.parent_category', 'warehouses.location', 'media', 'merchant.owner'])
            ->where('merchant_id', $merchant->id)
            ->where('id', $product->id)
            ->firstOrFail();
    }

    #[Computed]
    public function contact_number()
    {
        $validated_phone = $this->phonenumber_info($this->product->merchant->phone_number, $this->product->merchant->phone_iso);

        if ($validated_phone == false) {
            return $this->product->merchant->phone_number;
        }

        return '(+' . $validated_phone->getCountryCode() . ') ' . $validated_phone->getNationalNumber();
    }

    public function change_status()
    {
        try {
            $this->validate([
                'actionType' => 'required|in:approve,deny,reactivate,suspend',
            ]);
        } catch (ValidationException $th) {
            session()->flash('error', $th->getMessage());
            $this->visible = false;
            return;
        }
        
        DB::beginTransaction();
        try {
            $log = new AdminLog;
            $log->user_id = auth()->id();

            $notif = new Notification;
            $notif->recipient_id = $this->product->merchant->id;
            $notif->recipient_type = Merchant::class;
            $notif->ref_id = $this->product->sku;
            $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;

            switch ($this->actionType) {
                case 'approve':
                    $this->product->approval_status = 'approved';
                    $log->title = 'Approved product ' . $this->product->id;
                    $notif->message = "Your product, with SKU {$this->product->sku}, has been approved.";
                    break;
                case 'deny':
                    $this->product->approval_status = 'rejected';
                    $log->title = 'Rejected product ' . $this->product->id;
                    $notif->message = "Your product, with SKU {$this->product->sku}, has been rejected.";
                    break;
                case 'reactivate':
                    $this->product->approval_status = 'approved';
                    $log->title = 'Reactivated product ' . $this->product->id;
                    $notif->message = "Your product, with SKU {$this->product->sku}, has been reactivated.";
                    break;
                case 'suspend':
                    $this->product->approval_status = 'suspended';
                    $log->title = 'Suspended product ' . $this->product->id;
                    $notif->message = "Your product, with SKU {$this->product->sku}, has been suspended.";
                    break;
            }
            $this->product->is_active = false;
            $this->product->save();
            $notif->save();
            $log->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('AdminManageProductsShow.change_status: ' . $th->getMessage());
            $this->visible = false;
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
        $this->visible = false;
        return session()->flash('success', 'Product has been ' . $this->product->approval_status . '.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('admin.manage-products.admin-manage-products-show');
    }
}
