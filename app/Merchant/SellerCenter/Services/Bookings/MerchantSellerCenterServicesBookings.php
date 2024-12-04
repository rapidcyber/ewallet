<?php

namespace App\Merchant\SellerCenter\Services\Bookings;

use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\Merchant;
use App\Models\Notification;
use App\Models\NotificationModule;
use App\Models\Service;
use App\Models\User;
use App\Traits\WithCustomPaginationLinks;
use App\Traits\WithImage;
use App\Traits\WithNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;

class MerchantSellerCenterServicesBookings extends Component
{
    use WithPagination, WithCustomPaginationLinks, WithImage, WithNotification;
    
    public Merchant $merchant;
    public Service $service;
    #[Locked]
    public $categories;
    public $activeBox = 'all';
    public $searchTerm = '';
    public $dateFilter = '';
    #[Locked]
    public $sortBy = 'date_scheduled';
    #[Locked]
    public $sortDirection = 'desc';
    public $visible = false;
    public $showQuotationModal = false;
    public $actionType = '';
    public $booking_id = null;
    #[Locked]
    public $invoice_details = [];

    public function mount(Merchant $merchant, Service $service)
    {
        $this->merchant = $merchant;
        $this->service = $service;
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedDateFilter()
    {
        if (!in_array($this->dateFilter, ['', 'today', 'next_day', 'next_week', 'next_month', 'next_3_months', 'next_6_months', 'next_year'])) {
            $this->dateFilter = '';
        }

        $this->resetPage();
    }

    public function updatedActiveBox()
    {
        if (!in_array($this->activeBox, ['all', 'inquiries', 'pending', 'in_progress', 'fulfilled', 'cancelled'])) {
            $this->activeBox = 'all';
        }

        $this->resetPage();
    }

    public function sort($value)
    {
        if ($value === $this->sortBy) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            if (!in_array($value, ['date_scheduled', 'status'])) {
                $this->sortBy = 'date_scheduled';
            } else {
                $this->sortBy = $value;
            }

            $this->sortDirection = 'desc';
        }
    }

    #[Computed]
    public function booking_status()
    {
        return BookingStatus::all();
    }

    #[Computed]
    public function all_count()
    {
        return $this->service->bookings()->count() + $this->inquiries_count;
    }

    #[Computed]
    public function inquiries_count()
    {
        return $this->service->inquiries()->count();
    }

    #[Computed]
    public function pending_count()
    {
        return $this->service->bookings()->where('booking_status_id', $this->booking_status->where('slug', 'booked')->first()->id)->count();
    }

    #[Computed]
    public function in_progress_count()
    {
        return $this->service->bookings()->where('booking_status_id', $this->booking_status->where('slug', 'in_progress')->first()->id)->count();
    }

    #[Computed]
    public function fulfilled_count()
    {
        return $this->service->bookings()->where('booking_status_id', $this->booking_status->where('slug', 'fulfilled')->first()->id)->count();
    }

    #[Computed]
    public function cancelled_count()
    {
        return $this->service->bookings()->whereIn('booking_status_id', $this->booking_status->whereIn('slug', ['cancelled', 'declined'])->pluck('id')->toArray())->count();
    }

    public function change_status()
    {
        $booking = $this->service->bookings()->where('bookings.id', $this->booking_id)->first();

        if (!$booking) {
            session()->flash('error', 'Booking not found.');
            return;
        }

        switch ($this->actionType) {
            case 'accept':
                $this->accept($booking);
                break;
            case 'decline':
                $this->decline($booking);
                break;
            case 'cancel':
                $this->cancel($booking);
                break;
        }

        $this->visible = false;
    }

    public function updatedShowQuotationModal()
    {
        if ($this->showQuotationModal === false) {
            $this->reset('invoice_details');
        }
    }

    public function view_quotation($booking_id)
    {
        $booking = Booking::where('id', $booking_id)
            ->with(['invoice.items', 'invoice.inclusions'])
            ->wherehas('invoice')
            ->where('service_id', $this->service->id)
            ->select('invoice_id')
            ->first();

        if (!$booking) {
            session()->flash('error', 'Booking not found.');
            return;
        }

        $invoice = $booking->invoice;

        $sub_total = 0;
        $total = 0;

        foreach ($invoice->items as $item) {
            $this->invoice_details['items'][] = [
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total' => $item->price * $item->quantity,
            ];

            $sub_total += ($item->price * $item->quantity);
            $total += ($item->price * $item->quantity);
        }

        if ($invoice->inclusions->count() > 0) {
            foreach ($invoice->inclusions as $inclusion) {
                $this->invoice_details['inclusions'][] = [
                    'name' => $inclusion->name,
                    'amount' => $inclusion->amount,
                    'deduct' => $inclusion->deduct
                ];
    
                if ($inclusion->deduct == true) {
                    $total -= $inclusion->amount;
                } else {
                    $total += $inclusion->amount;
                }
            }
        } else {
            $this->invoice_details['inclusions'] = [];
        }

        $this->invoice_details['minimum_partial'] = $invoice->minimum_partial;
        $this->invoice_details['sub_total'] = $sub_total;
        $this->invoice_details['total'] = $total;

        $this->showQuotationModal = true;
    }

    private function accept(Booking $booking)
    {
        $allowed_status = $this->booking_status->where('slug', 'booked')->first()->id;

        if ($booking->booking_status_id === $allowed_status) {
            DB::beginTransaction();
            try {
                $booking->booking_status_id = $this->booking_status->where('slug', 'in_progress')->first()->id;
                $booking->save();

                $booking = $booking->load(['entity', 'service']);

                $this->alert(
                    $booking->entity,
                    'booking',
                    $booking->id,
                    "Your booking for service, {$booking->service->name}, has been accepted."
                );

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                Log::error("MerchantSellerCenterServicesBookings.accept: {$th->getMessage()}");
                return session()->flash('error', 'Something went wrong. Please try again later.');
            }

            session()->flash('success', 'Booking has been accepted.');
        } else {
            session()->flash('error', 'Booking must be in "Pending" status to accept.');
        }
    }

    private function decline(Booking $booking)
    {
        $allowed_status = $this->booking_status->whereIn('slug', ['inquiry', 'booked', 'quoted'])->pluck('id')->toArray();

        if (in_array($booking->booking_status_id, $allowed_status)) {
            DB::beginTransaction();
            try {
                $booking->booking_status_id = $this->booking_status->where('slug', 'declined')->first()->id;
                $booking->save();
    
                $booking = $booking->load(['entity', 'service']);

                $this->alert(
                    $booking->entity,
                    'booking',
                    $booking->id,
                    "Your booking for service, {$booking->service->name}, has been declined."
                );

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                Log::error("MerchantSellerCenterServicesBookings.decline: {$th->getMessage()}");
                return session()->flash('error', 'Something went wrong. Please try again later.');
            }

            session()->flash('success', 'Booking has been declined.');
        } else {
            session()->flash('error', 'Booking must be in "Inquiry", "Quotation Sent", or "Pending" status to decline.');
        }
    }

    private function cancel(Booking $booking)
    {
        $allowed_status = $this->booking_status->where('slug', 'in_progress')->first()->id;

        if ($booking->booking_status_id === $allowed_status) {
            DB::beginTransaction();
            try {
                $booking->booking_status_id = $this->booking_status->where('slug', 'cancelled')->first()->id;
                $booking->cancelled_by = 'merchant';
                $booking->save();
                
                $booking = $booking->load(['entity', 'service']);

                $this->alert(
                    $booking->entity,
                    'booking',
                    $booking->id,
                    "Your booking for service, {$booking->service->name}, has been cancelled."  
                );

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();
                Log::error("MerchantSellerCenterServicesBookings.cancel: {$th->getMessage()}");
                return session()->flash('error', 'Something went wrong. Please try again later.');
            }
            session()->flash('success', 'Booking has been cancelled.');
        } else {
            session()->flash('error', 'Booking must be in "In Progress" status to cancel.');
        }
    }

    private function get_date()
    {
        $date = [];
        $date['from'] = now()->startOfDay();
        switch ($this->dateFilter) {
            case 'today':
                $date['to'] = now()->endOfDay();
                break;
            case 'next_day':
                $date['to'] = now()->addDay()->endOfDay();
                break;
            case 'next_week':
                $date['to'] = now()->addWeek()->endOfDay();
                break;
            case 'next_month':
                $date['to'] = now()->addMonth()->endOfDay();
                break;
            case 'next_3_months':
                $date['to'] = now()->addMonths(3)->endOfDay();
                break;
            case 'next_6_months':
                $date['to'] = now()->addMonths(6)->endOfDay();
                break;
            case 'next_year':
                $date['to'] = now()->addYear()->endOfDay();
                break;
            default:
                $date['to'] = now()->endOfDay();
                break;
        }

        return $date;
    }

    #[Layout('layouts.merchant.seller-center')]
    public function render()
    {
        $bookings = Booking::where('service_id', $this->service->id)->with(['entity' => function (MorphTo $q) {
            $q->morphWith([
                User::class => ['profile', 'media' => function ($q) {
                    $q->where('collection_name', 'profile_picture');
                }],
                Merchant::class => ['media' => function ($q) {
                    $q->where('collection_name', 'merchant_logo');
                }],
            ]);
        }, 'location', 'status']);

        $bookings = match($this->activeBox) {
            'all' => $bookings,
            'inquiries' => $bookings->whereIn('booking_status_id', $this->booking_status->whereIn('slug', ['inquiry', 'quoted'])->pluck('id')->toArray()),
            'pending' => $bookings->where('booking_status_id', $this->booking_status->where('slug', 'booked')->first()->id),
            'in_progress' => $bookings->where('booking_status_id', $this->booking_status->where('slug', 'in_progress')->first()->id),
            'fulfilled' => $bookings->where('booking_status_id', $this->booking_status->where('slug', 'fulfilled')->first()->id),
            'cancelled' => $bookings->whereIn('booking_status_id', $this->booking_status->whereIn('slug', ['cancelled', 'declined'])->pluck('id')->toArray()),
        };

        if ($this->dateFilter) {
            $date = $this->get_date();

            $bookings = $bookings->whereBetween('service_date', [$date['from'], $date['to']]);
        }

        if ($this->searchTerm) {
            $bookings->where(function ($q) {
                $q->whereHasMorph('entity', User::class, function ($user) {
                    $user->whereHas('profile', function ($profile) {
                        $profile->where('first_name', 'like', '%' . $this->searchTerm . '%');
                        $profile->orWhere('surname', 'like', '%' . $this->searchTerm . '%');
                    });
                });
                $q->orWhereHasMorph('entity', Merchant::class, function ($merchant) {
                    $merchant->where('name', 'like', '%' . $this->searchTerm . '%');
                });
            });
        }

        $bookings = match ($this->sortBy) {
            'date_scheduled' => $bookings->orderBy('service_date', $this->sortDirection),
            'status' => $bookings->orderBy('booking_status_id', $this->sortDirection),
            default => $bookings->orderBy('service_date', $this->sortDirection),
        };

        $bookings = $bookings->paginate(15);

        $elements = $this->getPaginationElements($bookings);

        return view('merchant.seller-center.services.bookings.merchant-seller-center-services-bookings')->with([
            'bookings' => $bookings,
            'elements' => $elements,
        ]);
    }
}
