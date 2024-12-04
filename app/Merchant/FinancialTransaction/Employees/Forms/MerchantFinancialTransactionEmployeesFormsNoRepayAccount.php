<?php

namespace App\Merchant\FinancialTransaction\Employees\Forms;

use Livewire\Component;

class MerchantFinancialTransactionEmployeesFormsNoRepayAccount extends Component
{
    public $country_code, $country_code_options = [['label' => 'PH (+63)', 'value' => 'PH'], ['label' => 'SG (+65)', 'value' => 'SG']];

    public $gender;

    public $phone_number, $first_name, $surname;

    public function render()
    {
        return view('merchant.financial-transaction.employees.forms.merchant-financial-transaction-employees-forms-no-repay-account');
    }
}
