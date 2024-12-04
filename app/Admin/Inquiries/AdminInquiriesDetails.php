<?php

namespace App\Admin\Inquiries;

use App\Mail\InquiryReply;
use App\Models\AdminLog;
use App\Models\Inquiry;
use App\Traits\WithMail;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AdminInquiriesDetails extends Component
{
    use WithMail;
    public Inquiry $inquiry;
    public $errs = null;
    public $message;
    public $apiSuccessMsg;
    public $apiErrorMsg;
    public $subject = 'Inquiry Subject';

    public $modalVisible = false;

    public function mount(Inquiry $inquiry)
    {
        $this->inquiry = $inquiry;
    }

    public function delete()
    {
        DB::beginTransaction();

        try {
            $log = new AdminLog;
            $log->user_id = auth()->id();
            $log->title = 'Trashed inquiry ' . $this->inquiry->id . ': ' . $this->inquiry->full_name . ' (' . $this->inquiry->email . ')';
            $log->save();

            $this->inquiry->delete();
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('AdminInquiriesDetails.delete: ' . $ex->getMessage());
            return session()->flash('error', 'Inquiry could not be deleted. Please try again later.');
        }
        
        session()->flash('success', 'Inquiry deleted successfully.');
        return $this->redirect(route('admin.inquiries.index'));
    }

    public function onSend()
    {
        $this->validate([
            'message' => 'required|string|max:2000',
        ]);

        $inquiry = $this->inquiry;
        $inquiry->status = 1;
        try {
            DB::transaction(function () use ($inquiry) {
                $inquiry->save();
                $this->sendMail($this->inquiry->email, new InquiryReply(
                    $inquiry->full_name,
                    $inquiry->email,
                    $this->message
                ));

                $log = new AdminLog;
                $log->user_id = auth()->id();
                $log->title = 'Replied to inquiry ' . $inquiry->id . ': ' . $inquiry->full_name . ' (' . $inquiry->email . ')';
                $log->description = 'Response: ' . $this->message;
                $log->save();
            });
            
        } catch (Exception $ex) {
            Log::error('AdminInquiriesDetails.onSend: ' . $ex->getMessage());
            return session()->flash('error', 'Response could not be sent. Please try again later.');
        }

        $this->reset(['message']);
        return session()->flash('success', 'Response sent successfully.');
    }
    #[Layout('layouts.admin')]
    public function render()
    {

        return view('admin.inquiries.admin-inquiries-details');
    }
}
