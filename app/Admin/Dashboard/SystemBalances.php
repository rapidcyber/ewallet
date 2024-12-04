<?php

namespace App\Admin\Dashboard;

use App\Traits\WithAllBankFunctions;
use Livewire\Attributes\Layout;
use Livewire\Component;

class SystemBalances extends Component
{

    use WithAllBankFunctions;

    #[Layout('layouts.admin')]


    public $allbank = [
        'available_balance' => 0,
        'current_balance' => 0,
    ];

    public function render()
    {
        $this->get_allbank_balance();
        return view('admin.dashboard.system-balances');
    }


    public function get_allbank_balance()
    {
        $acct_no = config("services.alb.p2m");

        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'ACCOUNT-INQ',
            'acctno' => $acct_no,
        ];
        $str_xml = $this->generate_xml_string($request_body);

        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->allbank['available_balance'] = 'Response Failed';
        }

        $data = $this->get_xml_contents($response, false);
        if (empty($data['@attributes']['ErrorMsg']) == false) {
            return $this->allbank['available_balance'] = $data['@attributes']['ErrorMsg'];
        }

        $this->allbank['available_balance'] = $data['@attributes']['AvailableBalance'];
        $this->allbank['current_balance'] = $data['@attributes']['CurrentBalance'];
    }

    public function alb_opc_transactions() {
        $this->allbank_transaction_list('opc');
    }
    public function alb_p2m_transactions() {
        $this->allbank_transaction_list('p2m');
    }
    
    private function allbank_transaction_list(string $acc_type)
    {
        $acct_no = config("services.alb." . $acc_type);

        $start_date = $validated['start_date'] ?? now()->subWeek()->format('m/d/y');
        $end_date = $validated['end_date'] ?? now()->format('m/d/y');
        $trans_idcode = $validated['trans_idcode'] ?? 0;

        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'ACCOUNT-SOA',
            'acctno' => $acct_no,
            'ds' => $start_date,
            'de' => $end_date,
            'trans_idcode' => $trans_idcode,
        ];

        $str_xml = $this->generate_xml_string($request_body);
        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            dd("Response error", $response->body());
        }

        $data = $this->get_xml_contents($response, false);
        if (empty($data['@attributes']['ErrorMsg']) == false) {
            dd($data['@attributes']['ErrorMsg']);
        }

        if (empty($data['SOA'])) {
            $records = [];
        } else {
            $records = array_map(function ($record) {
                return $record['@attributes'];
            }, $data['SOA']['i']);
        }

        dd($records);
    }
}
