<?php

namespace App\User\Disputes;

use App\Models\Transaction;
use App\Models\TransactionDispute;
use App\Models\TransactionDisputeReason;
use App\Models\User;
use App\Traits\WithImageUploading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class UserDisputesCreate extends Component
{
    use WithFileUploads, WithImageUploading;

    public User $user;
    #[Locked]
    public $transaction_date;
    #[Locked]
    public $transaction_amount;
    #[Locked]
    public $validate_active = false;
    #[Locked]
    public $button_disabled = false;

    public $category;
    public $email;
    public $transaction_number;
    public $message;
    public $uploaded_files = [];

    public function mount()
    {
        $this->user = auth()->user();
    }

    public function rules()
    {
        return [
            'category' => 'required|exists:transaction_dispute_reasons,slug',
            'email' => 'required|email:rfc,dns',
            'transaction_number' => ['required', function ($attribute, $value, $fail) {
                if (!$this->user->outgoing_transactions()->where('txn_no', $value)->exists()) {
                    $fail('Invalid Transaction Reference Number');
                }
            }],
            'transaction_date' => 'required|date',
            'transaction_amount' => 'required|numeric',
            'message' => 'required|string|max:2000',
            'uploaded_files' => 'array|max:5',
            'uploaded_files.*' => 'array:name,image,size,id,order',
            'uploaded_files.*.name' => 'required',
            'uploaded_files.*.image' => 'image|mimes:png,jpg,jpeg|max:5120',
            'uploaded_files.*.size' => 'required',
            'uploaded_files.*.order' => 'required',
        ];
    }

    #[Computed(persist: true, seconds: 3600, cache: true, key: 'transaction_dispute_reasons')]
    public function categories()
    {
        return TransactionDisputeReason::orderBy('name')->toBase()->get();
    }

    public function updatedTransactionNumber()
    {
        $transaction = $this->user->outgoing_transactions()
            ->where('txn_no', $this->transaction_number)
            ->first();

        if ($transaction) {
            $this->transaction_date = $transaction->created_at->format('m/d/Y');
            $this->transaction_amount = $transaction->amount + $transaction->service_fee;
        } else {
            $this->transaction_date = null;
            $this->transaction_amount = null;
        }
    }

    public function updated($propertyName)
    {
        if ($this->validate_active) {
            $this->validateOnly($propertyName);
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

        $check_dispute = TransactionDispute::whereHas('transaction', function ($query) {
            $query->where('txn_no', $this->transaction_reference_number);
        })->first();

        if ($check_dispute && $check_dispute->status == 'pending') {
            $this->addError('transaction_number', 'Dispute already exists for this transaction.');
            session()->flash('error', 'Dispute already exists for this transaction.');
            session()->flash('error_message', 'Please wait for it to be resolved.');
            return;
        }

        $transaction_dispute = new TransactionDispute;

        $transaction_dispute->transaction_id = $this->user->outgoing_transactions()
            ->where('txn_no', $this->transaction_reference_number)
            ->first()
            ->id;

        $transaction_dispute->reason_id = $this->categories->where('slug', $this->category)->first()->id;
        $transaction_dispute->email = $this->email;
        $transaction_dispute->comment = $this->message;
        $transaction_dispute->status = 'pending';
        
        DB::beginTransaction();
        try {
            $transaction_dispute->save();
            foreach ($this->uploaded_files as $file) {
                $this->upload_file_media($transaction_dispute, $file['image'], 'dispute_images');
            }

            DB::commit();
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('UserDisputesCreate:submit - ' . $ex->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
            return;
        }

        $this->button_disabled = true;
        session()->flash('success', 'Dispute created successfully.');
        session()->flash('success_message', 'Please wait for it to be resolved.');
        return $this->redirect(route('user.disputes.index'));
    }

    #[Layout('layouts.user')]
    public function render()
    {
        return view('user.disputes.user-disputes-create');
    }
}
