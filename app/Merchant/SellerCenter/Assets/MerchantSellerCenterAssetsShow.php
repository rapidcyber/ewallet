<?php

namespace App\Merchant\SellerCenter\Assets;

use App\Models\Merchant;
use App\Models\Product;
use App\Traits\WithImage;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

class MerchantSellerCenterAssetsShow extends Component
{
    use WithImage;

    public Merchant $merchant;
    public Product $product;
    public $visible = false;
    #[Locked]
    public $can_delete = false;
    #[Locked]
    public $can_edit = false;

    public function mount(Merchant $merchant, Product $product)
    {
        $this->merchant = $merchant;
        $this->product = $product->load([
            'category.parent_category',
            'warehouses.location',
            'media',
            'condition',
            'productDetail'
        ]);

        if (Gate::allows('merchant-products', [$this->merchant, 'delete'])) {
            $this->can_delete = true;
        }

        if (Gate::allows('merchant-products', [$this->merchant, 'update'])) {
            $this->can_edit = true;
        }
    }

    public function delete()
    {
        if ($this->product->merchant_id !== $this->merchant->id) {
            $this->visible = false;
            return session()->flash('error', 'Something went wrong. Please try again later.');
        }

        $this->product->delete();

        session()->flash('success', 'Product deleted successfully');
        return $this->redirect(route('merchant.seller-center.assets.index', ['merchant' => $this->merchant]));
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.assets.merchant-seller-center-assets-show');
    }
}
