<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use App\Models\QrGeneratedData;
use App\Models\TransactionChannel;
use App\Models\TransactionProvider;
use App\Models\TransactionType;
use App\Traits\WithAllBankFunctions;
use App\Traits\WithHttpResponses;
use App\Traits\WithNumberGeneration;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PartnersController extends Controller
{

    use WithHttpResponses, WithNumberGeneration, WithAllBankFunctions;
    public function generate_qr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:100|max:20000',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first(), 401);
        }

        $partner = auth('api')->client()->user;

        $amount = (float) $validator->getValue('amount') ?? null;
        $is_static = empty($amount) ? true : false;

        /// If static, return existing generated static qr of user if there is any.
        if ($is_static) {
            $qr = $partner->generated_qrs()->where([
                'internal' => false,
            ])->first();
            if (empty($qr) == false) {
                return $this->success([
                    'token' => $qr->merc_token,
                    'is_static' => $qr->type == 'static' ? true : false,
                    'qr' => $qr->code,
                ]);
            }
        }

        $provider = TransactionProvider::where('slug', 'allbank')->first();
        $channel = TransactionChannel::where('slug', 'external_inbound')->first();
        $transaction_type = TransactionType::where('slug', 'cash_in')->first();
        $ref_no = $this->generate_transaction_reference_number($provider, $channel, $transaction_type);

        [$token, $tdt] = $this->generate_token();
        $request_body = [
            'id' => config('services.alb.api_id'),
            'tdt' => $tdt,
            'token' => $token,
            'cmd' => 'MERC-QR-REQ',
            'rf' => $ref_no,
            'amt' => $amount ?? '0',
            'merc_tid' => '0',
            'make_static_qr' => $is_static ? '1' : '0',
        ];
        $str_xml = $this->generate_xml_string($request_body);
        $response = $this->handle_post($str_xml);
        if ($response->failed()) {
            return $this->error(
                json_decode($response->body(), true),
                499,
            );
        }

        $data = $this->get_xml_contents($response);
        if ($data['ReturnCode'] != 0) {
            return $this->error($data['ErrorMsg'], 499);
        }

        DB::beginTransaction();
        try {
            $QrData = new QrGeneratedData();
            $QrData->fill([
                'client_id' => $partner->id,
                'client_type' => get_class($partner),
                'ref_no' => $ref_no,
                'merc_token' => $data['merc_token'],
                'type' => $is_static ? 'static' : 'dynamic',
                'internal' => false,
                'code' => $data['qrph'],
            ]);
            $QrData->save();

            DB::commit();
            return $this->success([
                'token' => $data['merc_token'],
                'is_static' => $is_static,
                'amount' => $amount,
                'qr' => $data['qrph'],
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return $this->exception($ex);
        }
    }
}
