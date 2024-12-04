<?php

namespace App\Admin\Inquiries;

use App\Models\AdminLog;
use App\Models\Inquiry;
use App\Traits\WithCustomPaginationLinks;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class AdminInquiries extends Component
{
    use WithCustomPaginationLinks, WithPagination;
    public $searchTerm = '';
    #[Locked]
    public $orderBy = 'desc';
    public $activeBox = 'UNANSWERED';
    public $selectAll = false;
    public $checkedInquiries = [];
    public $confirmationModalVisible = false;
    public $actionType = '';
    public $selected_inquiry_id = null;

    public $groupConfirmationModalVisible = false;
    public $groupActionType = '';

    protected $status = ['UNANSWERED', 'RESPONDED', 'TRASH'];
    
    public function handleSelectAllCheckbox($checked, $inquiry_ids)
    {
        $this->checkedInquiries = $checked ? $inquiry_ids : [];
    }


    public function handleSingleSelectCheckbox($inquiry_count)
    {
        $this->selectAll = count($this->checkedInquiries) === $inquiry_count ? true : false;
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();

        $this->reset(['orderBy']);
    }

    public function updatedActiveBox()
    {
        $this->resetPage();

        if (! in_array($this->activeBox, $this->status)) {
            $this->activeBox = 'UNANSWERED';
        }

        $this->reset(['orderBy']);
    }

    public function toggleSortDirection()
    {
        $this->orderBy = $this->orderBy === 'desc' ? 'asc' : 'desc';
    }

    #[Computed]
    public function count_unanswered()
    {
        return Inquiry::where('status', 0)->count();
    }

    #[Computed]
    public function count_responded()
    {
        return Inquiry::where('status', 1)->count();
    }

    #[Computed]
    public function count_trash()
    {
        return Inquiry::withTrashed()->whereNotNull('deleted_at')->count();
    }

    public function change_status()
    {
        if ($this->selected_inquiry_id == null) {
            session()->flash('error', 'Please select an inquiry to change status');
            $this->confirmationModalVisible = false;
            return;
        }

        DB::beginTransaction();
        try {
            $log = new AdminLog;
            $log->user_id = auth()->id();

            switch ($this->actionType) {
                case 'delete':
                    $inquiry = Inquiry::where('id', $this->selected_inquiry_id)->firstOrFail();
                    if ($inquiry) {
                        $inquiry->delete();
                        $log->title = 'Trashed inquiry ' . $inquiry->id . ': ' . $inquiry->full_name . ' (' . $inquiry->email . ')';
                        $log->save();
                    } else {
                        $this->confirmationModalVisible = false;
                        return session()->flash('error', 'Inquiry not found');
                    }
                    break;
    
                case 'restore':
                    $inquiry = Inquiry::withTrashed()->where('id', $this->selected_inquiry_id)->firstOrFail();
                    if ($inquiry) {
                        $inquiry->restore();
                        $log->title = 'Restored inquiry ' . $inquiry->id . ': ' . $inquiry->full_name . ' (' . $inquiry->email . ')';
                        $log->save();
                    } else {
                        $this->confirmationModalVisible = false;
                        return session()->flash('error', 'Inquiry not found');
                    }
                    break;
                default:
                    $this->confirmationModalVisible = false;
                    return session()->flash('error', 'Please select an action type');
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminInquiries.change_status: ' . $th->getMessage());
            $this->confirmationModalVisible = false;
            return session()->flash('error', 'Inquiry could not be ' . $this->actionType . 'd. Please try again later.');
        }

        session()->flash('success', 'Inquiry has been ' . $this->actionType . 'd');
        return $this->confirmationModalVisible = false;
    }

    public function group_change_status()
    {
        if (empty($this->checkedInquiries)) {
            session()->flash('error', 'Please select an inquiry to change status');
            $this->groupConfirmationModalVisible = false;
            return;
        }

        DB::beginTransaction();
        try {
            $log = new AdminLog;
            $log->user_id = auth()->id();
            $inquiries = Inquiry::whereIn('id', $this->checkedInquiries)->get();
            switch ($this->groupActionType) {
                case 'delete':
                    foreach ($inquiries as $inquiry) {
                        $inquiry->delete();
                        $log->title = 'Trashed inquiry ' . $inquiry->id . ': ' . $inquiry->full_name . ' (' . $inquiry->email . ')';
                        $log->save();
                    }
                    break;
                case 'restore':
                    foreach ($inquiries as $inquiry) {
                        $inquiry->restore();
                        $log->title = 'Restored inquiry ' . $inquiry->id . ': ' . $inquiry->full_name . ' (' . $inquiry->email . ')';
                        $log->save();
                    }
                    break;
                default:
                    $this->groupConfirmationModalVisible = false;
                    return session()->flash('error', 'Please select an action type');
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('AdminInquiries.group_change_status: ' . $th->getMessage());
            $this->groupConfirmationModalVisible = false;
            return session()->flash('error', 'Inquiries could not be ' . $this->groupActionType . 'd. Please try again later.');
        }

        $this->checkedInquiries = [];
        $this->selectAll = false;
        session()->flash('success', 'Inquiries have been ' . $this->groupActionType . 'd');
        return $this->groupConfirmationModalVisible = false;
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $inquiries = Inquiry::query();

        $inquiries = match($this->activeBox) {
            'UNANSWERED' => $inquiries->where('status', 0),
            'RESPONDED' => $inquiries->where('status', 1),
            'TRASH' => $inquiries->withTrashed()->whereNotNull('deleted_at'),
            default => $inquiries->where('status', 0),
        };

        if ($this->searchTerm) {
            $inquiries = $inquiries->where(function ($query) {
                $query->where('full_name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $inquiries = $inquiries->orderBy('created_at', $this->orderBy)->paginate(10);
        $elements = $this->getPaginationElements($inquiries);

        return view('admin.inquiries.admin-inquiries')->with([
            'inquiries' => $inquiries,
            'elements' => $elements
        ]);
    }
}
