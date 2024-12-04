<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Merchant\MerchantOnboardRequest;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Location;
use App\Models\Merchant;
use App\Models\MerchantCategory;
use App\Models\MerchantDetail;
use App\Models\Role;
use App\Traits\WithHttpResponses;
use App\Traits\WithImageUploading;
use App\Traits\WithNumberGeneration;
use App\Traits\WithValidPhoneNumber;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MerchantOnboardController extends Controller
{

    use WithHttpResponses, WithNumberGeneration, WithValidPhoneNumber, WithImageUploading;

    /**
     * Handle the incoming request.
     */
    public function __invoke(MerchantOnboardRequest $request)
    {
        $validated = $request->validated();
        $is_eligible = auth()->user()->roles()->where('slug', 'verified_user')->exists();
        if ($is_eligible == false) {
            return $this->error('Ineligible, your account must be verified', 499);
        }

        if (auth()->user()->merchants()->count() >= 5) {
            return $this->error('Max merchant accounts limit reached', 499);
        }

        $phone_info = $this->phonenumber_info($validated['phone_number'], $validated['phone_iso']);
        if ($phone_info == false) {
            return $this->error('Invalid phone number', 499);
        }

        $merchant = new Merchant;
        $merchant->fill([
            'user_id' => auth()->user()->id,
            'app_id' => Str::orderedUuid(),
            'account_number' => $this->generate_merchant_account_number(auth()->user()),
            'merchant_category_id' => MerchantCategory::where('slug', $validated['category'])->first()->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_iso' => $validated['phone_iso'],
            'phone_number' => $phone_info->getCountryCode() . $phone_info->getNationalNumber(),
            'invoice_prefix' => $validated['invoice_prefix'] ?? $this->generate_invoice_prefix(),
        ]);

        /// create employee details
        $employee = new Employee;
        $employee->fill([
            'user_id' => auth()->user()->id,
            'employee_role_id' => EmployeeRole::where('slug', 'owner')->first()->id,
            'occupation' => 'Owner',
            'salary' => 0,
            'salary_type_id' => 1,
        ]);

        $location = new Location;
        $location->fill($validated['location']);

        $birCor = $validated['bir_cor'];
        $dtiSec = $validated['dti_sec'];

        try {
            DB::transaction(function () use ($merchant, $location, $employee, $birCor, $dtiSec) {
                $merchant->save();

                MerchantDetail::firstOrCreate(['merchant_id' => $merchant->id]);

                $merchant->roles()->syncWithoutDetaching([Role::where('slug', str('Merchant')->slug('_'))->first()->id]);
                $employee->merchant_id = $merchant->id;
                $employee->save();

                $this->upload_file_media($merchant, $birCor, 'bir_cor');
                $this->upload_file_media($merchant, $dtiSec, 'dti_sec');

                $location->entity_id = $merchant->id;
                $location->entity_type = get_class($merchant);
                $location->save();
            });

            $merchant->category;
            $merchant->status = 'pending';
            return $this->success($merchant);
        } catch (Exception $ex) {
            return $this->exception($ex);
        }
    }
}
