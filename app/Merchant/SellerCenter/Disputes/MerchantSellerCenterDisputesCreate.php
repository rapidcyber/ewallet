<?php

namespace App\Merchant\SellerCenter\Disputes;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\TransactionDispute;
use App\Models\TransactionDisputeReason;
use App\Traits\WithImageUploading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class MerchantSellerCenterDisputesCreate extends Component
{
    use WithFileUploads, WithImageUploading;

    #[Locked]
    public $categories = [];

    public Merchant $merchant;
    public $category;
    public $email;
    public $transaction_reference_number;
    public $message;
    #[Locked]
    public $uploaded_files = [];

    #[Locked]
    public $transaction_date;
    #[Locked]
    public $transaction_amount;
    #[Locked]
    public $validate_active = false;

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant;

        $this->categories = TransactionDisputeReason::orderBy('name')->get(['id', 'name']);
    }

    public function rules()
    {
        return [
            'category' => 'required|exists:transaction_dispute_reasons,id',
            'email' => 'required|email:rfc,dns',
            'transaction_reference_number' => 'required|exists:transactions,ref_no',
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric',
            'message' => 'required|min:15|max:2000',
            'uploaded_files' => 'array|max:5',
            'uploaded_files.*' => 'array:name,image,size,id,order',
            'uploaded_files.*.name' => 'required',
            'uploaded_files.*.image' => 'image|mimes:png,jpg,jpeg|max:5120',
            'uploaded_files.*.size' => 'required',
            'uploaded_files.*.order' => 'required',
        ];
    }

    public function updated($propertyName)
    {
        if ($this->validate_active) {
            $this->validateOnly($propertyName);
        }
    }

    public function updatedTransactionReferenceNumber()
    {
        $check_transaction = Transaction::where('ref_no', $this->transaction_reference_number)
            ->whereHasMorph('sender', Merchant::class, function ($q) {
                $q->where('id', $this->merchant->id);
            })
            ->first();

        if ($check_transaction) {
            $this->transaction_date = $check_transaction->created_at->format('m/d/Y');
            $this->transaction_amount = $check_transaction->amount;
        } else {
            $this->transaction_date = null;
            $this->transaction_amount = null;
        }
    }

    #[On('updateUploadedFiles')]
    public function updateUploadedFiles($images)
    {
        $this->uploaded_files = $images;

        foreach($this->uploaded_files as $key => $image) {
            $this->uploaded_files[$key]['image'] = new TemporaryUploadedFile($image['image'], config('filesystems.default'));
        }
    }

    public function submit()
    {
        try {
            $this->validate();
        } catch (ValidationException $ex) {
            $this->setErrorBag($ex->validator->errors());
            return $this->validate_active = true;
        }

        $this->validate_active = false;

        $transaction = Transaction::where('ref_no', $this->transaction_reference_number)
            ->whereHasMorph('sender', Merchant::class, function ($q) {
                $q->where('id', $this->merchant->id);
            })
            ->first();

        if (! $transaction) {
            session()->flash('error', 'Transaction not found');
            return;
        }

        $transaction_dispute = new TransactionDispute;
        $transaction_dispute->transaction_id = $transaction->id;
        $transaction_dispute->reason_id = $this->category;
        $transaction_dispute->email = $this->email;
        $transaction_dispute->comment = $this->message;

        DB::beginTransaction();
        try {
            $transaction_dispute->save();

            if ($this->uploaded_files) {
                foreach ($this->uploaded_files as $attachment) {
                    $this->upload_file_media($transaction_dispute, $attachment['image'], 'dispute_images');
                }
            }
            DB::commit();

            session()->flash('success', 'Dispute created successfully');
            return $this->redirect(route('merchant.seller-center.disputes.index', ['merchant' => $this->merchant]));
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('MerchantSellerCenterDisputesCreate.submit: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }
    }
    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        return view('merchant.seller-center.disputes.merchant-seller-center-disputes-create');
    }
}
