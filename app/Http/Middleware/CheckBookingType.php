<?php

namespace App\Http\Middleware;

use App\Models\Booking;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBookingType
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $type = $request->route('type');
        $booking = $request->route('booking')->id;

        $bookingModel = Booking::where('id', $booking)->first();

        if (($type === 'inquiries' && $bookingModel->booking_status_id !== 1) ||
            ($type === 'bookings' && $bookingModel->booking_status_id === 1)) {
            abort(404);
        }

        return $next($request);
    }
}
