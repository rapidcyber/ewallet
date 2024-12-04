<?php

namespace App\Admin\ManageMerchants\Show\Products;

use App\Models\AdminLog;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\Product;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminManageMerchantsShowProducts extends Component
{
    use WithPagination, WithCustomPaginationLinks;

    public Merchant $merchant;
    public $searchTerm = '';

    #[Locked]
    public $sortBy = 'created_at';
    #[Locked]
    public $sortDirection = 'desc';

    public $confirmationModalVisible = false;
    public $actionType = '';
    public $product_id;

    public $groupConfirmationModalVisible = false;
    public $groupActionType = '';
    public $selectAll = false;
    public $checkedProducts = [];

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;
    }

    public function sortTable($fieldName)
    {
        if ($this->sortBy !== $fieldName) {
            if (!in_array($fieldName, ['created_at', 'price'])) {
                $this->sortBy;
            } else {
                $this->sortBy = $fieldName;
            }
            $this->sortDirection = 'desc';
        } else {
            if ($this->sortDirection === 'desc') {
                $this->sortDirection = 'asc';
            } elseif ($this->sortDirection === 'asc') {
                $this->sortDirection = 'desc';
            }
        }
    }

    public function handleSelectAllCheckbox($checked, $products)
    {
        if ($checked) {
            $this->checkedProducts = $products;
        } else {
            $this->checkedProducts = [];
        }
    }

    public function handleSingleSelectCheckbox($products)
    {
        if (count($this->checkedProducts) === count($products)) {
            $this->selectAll = true;
        } else {
            $this->selectAll = false;
        }
    }

    public function multipleActivate()
    {
        $this->validate([
            'checkedProducts' => 'required|array|min:1|max:10',
            'checkedProducts.*' => 'required|exists:products,id',
        ]);

        $products = Product::where('merchant_id', $this->merchant->id)
            ->whereIn('id', $this->checkedProducts)
            ->with(['merchant.owner'])
            ->get();

        DB::beginTransaction();
        try {
            foreach($products as $product) {
                $product->approval_status = 'approved';
                $product->save();

                $notif = new Notification;
                $notif->recipient_id = $product->merchant->id;
                $notif->recipient_type = Merchant::class;
                $notif->ref_id = $product->sku;
                $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
                $notif->message = "Your product, with SKU {$product->sku}, has been approved.";
                $notif->save();
            }

            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Activated products ' . implode(',', $this->checkedProducts);
            $log->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsShowProducts.multipleActivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }
        
        session()->flash('success', 'Services have been activated.');
        $this->reset(['checkedProducts', 'selectAll', 'groupConfirmationModalVisible']);
        return;
    }

    public function multipleDeactivate()
    {
        $this->validate([
            'checkedProducts' => 'required|array|min:1|max:10',
            'checkedProducts.*' => 'required|exists:products,id',
        ]);

        $products = Product::where('merchant_id', $this->merchant->id)
            ->whereIn('id', $this->checkedProducts)
            ->with(['merchant.owner'])
            ->get();

        DB::beginTransaction();
        try {
            foreach($products as $product) {
                $product->approval_status = 'suspended';
                $product->is_active = false;
                $product->save();
            }

            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Deactivated products ' . implode(',', $this->checkedProducts);
            $log->save();

            $notif = new Notification;
            $notif->recipient_id = $product->merchant->id;
            $notif->recipient_type = Merchant::class;
            $notif->ref_id = $product->sku;
            $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
            $notif->message = "Your product, with SKU {$product->sku}, has been suspended.";
            $notif->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageMerchantsShowProducts.multipleDeactivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }
        
        session()->flash('success', 'Products have been deactivated.');
        $this->reset(['checkedProducts', 'selectAll', 'groupConfirmationModalVisible']);
        return;
    }

    public function change_status()
    {
        try {
            $this->validate([
                'product_id' => 'required|exists:products,id',
                'actionType' => 'required|in:approve,deny,reactivate,deactivate',
            ]);
        } catch (ValidationException $th) {
            session()->flash('error', $th->getMessage());
            $this->confirmationModalVisible = false;
            return;
        }
        
        DB::beginTransaction();
        try {
            $product = Product::where('merchant_id', $this->merchant->id)
                ->with(['merchant.owner'])
                ->find($this->product_id);
            $log = new AdminLog;
            $log->user_id = auth()->id();

            $notif = new Notification;
            $notif->recipient_id = $product->merchant->id;
            $notif->recipient_type = Merchant::class;
            $notif->ref_id = $product->sku;
            $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
            
            switch ($this->actionType) {
                case 'approve':
                    $product->approval_status = 'approved';
                    $log->title = 'Approved product ' . $product->id;
                    $notif->message = "Your product, with SKU {$product->sku}, has been approved.";
                    break;
                case 'deny':
                    $product->approval_status = 'rejected';
                    $log->title = 'Rejected product ' . $product->id;
                    $notif->message = "Your product, with SKU {$product->sku}, has been rejected.";
                    break;
                case 'reactivate':
                    $product->approval_status = 'approved';
                    $log->title = 'Reactivated product ' . $product->id;
                    $notif->message = "Your product, with SKU {$product->sku}, has been reactivated.";
                    break;
                case 'deactivate':
                    $product->approval_status = 'suspended';
                    $log->title = 'Deactivated product ' . $product->id;
                    $notif->message = "Your product, with SKU {$product->sku}, has been suspended.";
                    break;
            }
            $product->is_active = false;
            $product->save();

            $log->save();

            $notif->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('AdminManageMerchantsShowProducts.change_status: ' . $th->getMessage());
            $this->confirmationModalVisible = false;
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
        $this->confirmationModalVisible = false;
        return session()->flash('success', 'Product has been ' . $product->approval_status . '.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $products = Product::where('merchant_id', $this->merchant->id);

        if ($this->searchTerm) {
            $products = $products->where('name', 'like', '%' . $this->searchTerm . '%');
        }

        $products = $products->select([
            'id',
            'merchant_id',
            'name',
            'price',
            'created_at',
            'approval_status',
            'is_active',
        ])
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        $elements = $this->getPaginationElements($products);

        return view('admin.manage-merchants.show.products.admin-manage-merchants-show-products')->with([
            'products' => $products,
            'elements' => $elements,
        ]);
    }
}
