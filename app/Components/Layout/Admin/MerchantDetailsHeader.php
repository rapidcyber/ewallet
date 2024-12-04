<?php

namespace App\Components\Layout\Admin;

use App\Models\AdminLog;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\User;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class MerchantDetailsHeader extends Component
{
    use WithImage, WithNotification;

    public Merchant $merchant;
    public $visible = false;
    public $actionType = '';

    public function mount(Merchant $merchant)
    {
        $this->merchant = $merchant->load(['owner:id']);
    }

    public function change_status()
    {
        if ($this->merchant->id === 1) {
            session()->flash('warning', 'No actions can be performed on this merchant.');
            return $this->visible = false;
        }

        DB::beginTransaction();
        try {
            switch ($this->actionType) {
                case 'approve':
                    if ($this->merchant->status == 'pending') {
                        $this->merchant->status = 'verified';
                        $this->merchant->save();
    
                        session()->flash('success', 'Merchant has been approved.');

                        $log = new AdminLog;
                        $log->user_id = auth()->id();
                        $log->title = 'Approved merchant ' . $this->merchant->id;
                        $log->save();

                        $this->alert(
                            $this->merchant->owner,
                            'notification',
                            $this->merchant->account_number,
                            "Your merchant account, '{$this->merchant->name}', has been approved.",
                            
                        );
                    } else {
                        session()->flash('warning', 'Only pending merchants can be approved.');
                    }
                    break;
                case 'reactivate':
                    if (in_array($this->merchant->status, ['rejected', 'deactivated'])) {
                        $this->merchant->status = 'verified';
                        $this->merchant->save();
    
                        session()->flash('success', 'Merchant has been reactivated.');

                        $log = new AdminLog;
                        $log->user_id = auth()->id();
                        $log->title = 'Reactivated merchant ' . $this->merchant->id;
                        $log->save();

                        $this->alert(
                            $this->merchant->owner,
                            'notification',
                            $this->merchant->account_number,
                            "Your merchant account, '{$this->merchant->name}', has been reactivated.",
                            
                        );
                    } else {
                        session()->flash('warning', 'Only rejected and deactivated merchants can be reactivated.');
                    }
                    break;
                case 'deny':
                    if ($this->merchant->status == 'pending') {
                        $this->merchant->status = 'rejected';
                        $this->merchant->save();
    
                        session()->flash('success', 'Merchant has been rejected.');

                        $log = new AdminLog;
                        $log->user_id = auth()->id();
                        $log->title = 'Denied merchant ' . $this->merchant->id;
                        $log->save();

                        $this->alert(
                            $this->merchant->owner,
                            'notification',
                            $this->merchant->account_number,
                            "Your merchant account, '{$this->merchant->name}', has been rejected.",
                            
                        );
                    } else {
                        session()->flash('warning', 'Only pending merchants can be rejected.');
                    }
                    break;
                case 'deactivate':
                    if ($this->merchant->status == 'verified') {
                        $this->merchant->status = 'deactivated';
                        $this->merchant->save();
    
                        session()->flash('success', 'Merchant has been deactivated.');

                        $log = new AdminLog;
                        $log->user_id = auth()->id();
                        $log->title = 'Deactivated merchant ' . $this->merchant->id;
                        $log->save();

                        $this->alert(
                            $this->merchant->owner,
                            'notification',
                            $this->merchant->account_number,
                            "Your merchant account, '{$this->merchant->name}', has been deactivated.",
                            
                        );
                    } else {
                        session()->flash('warning', 'Only verified merchants can be deactivated.');
                    }
                    break;
                default:
                    return session()->flash('warning', 'Invalid action.');
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('MerchantDetailsHeader: ' . $th->getMessage());
            session()->flash('error', 'Something went wrong. Please try again later.');
        }

        $this->visible = false;
    }

    public function render()
    {
        return view('components.layout.admin.merchant-details-header');
    }
}
