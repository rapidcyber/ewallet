<?php

namespace App\Admin\ManageProducts;

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

class AdminManageProducts extends Component
{
    use WithPagination, WithCustomPaginationLinks;

    public $searchTerm = '';
    #[Locked]
    public $sortBy = 'created_at';
    #[Locked]
    public $sortDirection = 'desc';

    public $selectedBox = 'all';

    public $confirmationModalVisible = false;
    public $actionType = '';
    public $product_id;

    public $selectAll = false;
    public $checkedProducts = [];
    public $groupConfirmationModalVisible = false;
    public $groupActionType = '';

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

    public function updatedSearchTerm()
    {
        $this->resetPage();
        $this->reset(['sortBy', 'sortDirection']);
        $this->checkedProducts = [];
    }

    public function sortTable($field)
    {
        if ($this->sortBy == $field) {
            $this->sortDirection = $this->sortDirection == 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = in_array($field, ['created_at', 'price']) ? $field : 'created_at';
            $this->sortDirection = 'desc';
        }
    }

    public function updatedSelectedBox()
    {
        $this->resetPage();
        $this->reset(['sortBy', 'sortDirection']);

        if (!in_array($this->selectedBox, ['all', 'review', 'active', 'rejected', 'suspended', 'unpublished'])) {
            $this->selectedBox = 'all';
        }

        $this->checkedProducts = [];
    }

    public function multipleActivate()
    {
        try {
            $this->validate([
                'checkedProducts' => 'required|array|min:1|max:10',
                'checkedProducts.*' => 'required|exists:products,id',
            ]);
        } catch (ValidationException $th) {
            session()->flash('error', $th->getMessage());
            $this->confirmationModalVisible = false;
            return;
        }

        $products = Product::whereIn('id', $this->checkedProducts)
            ->with(['merchant.owner'])
            ->get();

        DB::beginTransaction();
        try {
            foreach ($products as $product) {
                $product->approval_status = 'approved';
                $product->save();

                $notif = new Notification;
                $notif->recipient_id = $product->merchant->id;
                $notif->recipient_type = Merchant::class;
                $notif->ref_id = $product->sku;
                $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
                $notif->message = "Your product with SKU, {$product->sku} has been activated/reactivated.";
                $notif->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageProducts.multipleActivate: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        session()->flash('success', 'Products have been activated.');
        $this->reset(['checkedProducts', 'selectAll', 'groupConfirmationModalVisible']);
        return;
    }

    public function multipleDeactivate()
    {
        try {
            $this->validate([
                'checkedProducts' => 'required|array|min:1|max:10',
                'checkedProducts.*' => 'required|exists:products,id',
            ]);
        } catch (ValidationException $th) {
            session()->flash('error', $th->getMessage());
            $this->confirmationModalVisible = false;
            return;
        }

        $products = Product::whereIn('id', $this->checkedProducts)
            ->with(['merchant.owner'])
            ->get();

        DB::beginTransaction();
        try {
            foreach ($products as $product) {
                $product->approval_status = 'suspended';
                $product->is_active = false;
                $product->save();

                $notif = new Notification;
                $notif->recipient_id = $product->merchant->id;
                $notif->recipient_type = Merchant::class;
                $notif->ref_id = $product->sku;
                $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;
                $notif->message = "Your product with SKU, {$product->sku} has been deactivated.";
                $notif->save();
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminManageProducts.multipleDeactivate: ' . $th->getMessage());
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
            $product = Product::with(['merchant'])->find($this->product_id);

            $notif = new Notification;
            $notif->recipient_id = $product->merchant->id;
            $notif->recipient_type = Merchant::class;
            $notif->ref_id = $product->sku;
            $notif->notification_module_id = NotificationModule::where('slug', 'notification')->firstOrFail()->id;

            switch ($this->actionType) {
                case 'approve':
                    $product->approval_status = 'approved';
                    $notif->message = "Your product with SKU, {$product->sku} has been approved.";
                    break;
                case 'deny':
                    $product->approval_status = 'rejected';
                    $notif->message = "Your product with SKU, {$product->sku} has been rejected.";
                    break;
                case 'reactivate':
                    $product->approval_status = 'approved';
                    $notif->message = "Your product with SKU, {$product->sku} has been reactivated.";
                    break;
                case 'deactivate':
                    $product->approval_status = 'suspended';
                    $notif->message = "Your product with SKU, {$product->sku} has been suspended.";
                    break;
            }
            $product->is_active = false;
            $product->save();

            $notif->save();
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('AdminManageProducts.change_status: ' . $th->getMessage());
            $this->confirmationModalVisible = false;
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }
        $this->confirmationModalVisible = false;
        return session()->flash('success', 'Product has been ' . $product->approval_status . '.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $products = Product::query();

        $totalProductCount = $products->clone()->count();
        $reviewCount = $products->clone()->where('approval_status', 'review')->count();
        $activeCount = $products->clone()->where('approval_status', 'approved')->where('is_active', true)->count();
        $rejectedCount = $products->clone()->where('approval_status', 'rejected')->count();
        $suspendedCount = $products->clone()->where('approval_status', 'suspended')->count();
        $unpublishedCount = $products->clone()->where('approval_status', 'approved')->where('is_active', false)->count();

        $products = match ($this->selectedBox) {
            'all' => $products,
            'review' => $products->where('approval_status', 'review'),
            'active' => $products->where('approval_status', 'approved')->where('is_active', true),
            'rejected' => $products->where('approval_status', 'rejected'),
            'suspended' => $products->where('approval_status', 'suspended'),
            'unpublished' => $products->where('approval_status', 'approved')->where('is_active', false),
        };

        if ($this->searchTerm) {
            $products = $products->where('name', 'like', '%' . $this->searchTerm . '%')
                ->orWhereHas('merchant', function ($query) {
                    $query->where('name', 'like', '%' . $this->searchTerm . '%');
                });
        }

        $products = $products->with(['merchant'])->orderBy($this->sortBy, $this->sortDirection)->paginate(10);

        $elements = $this->getPaginationElements($products);

        return view('admin.manage-products.admin-manage-products')->with([
            'products' => $products,
            'elements' => $elements,
            'totalProductCount' => $totalProductCount,
            'reviewCount' => $reviewCount,
            'activeCount' => $activeCount,
            'rejectedCount' => $rejectedCount,
            'suspendedCount' => $suspendedCount,
            'unpublishedCount' => $unpublishedCount,
        ]);
    }
}
