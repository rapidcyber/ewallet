<?php

namespace App\Guest\ContactUs;

use App\Mail\InquirySuccess;
use App\Mail\RepayMail;
use App\Models\Inquiry;
use App\Traits\WithMail;
use App\Traits\WithNumberGeneration;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ContactUs extends Component
{
    use WithMail, WithNumberGeneration;

    public $name;

    public $email;

    public $subject;

    public $message;

    public $errs = null;

    public $apiSuccessMsg;

    public $apiErrorMsg;

    public $recaptcha;

    public function updatedName()
    {
        if (! empty($this->errs['full_name'])) {
            unset($this->errs['full_name']);
        }
    }

    public function updatedEmail()
    {
        if (! empty($this->errs['email'])) {
            unset($this->errs['email']);
        }
    }

    public function updatedSubject()
    {
        if (! empty($this->errs['subject'])) {
            unset($this->errs['subject']);
        }
    }

    public function updatedMessage()
    {
        if (! empty($this->errs['message'])) {
            unset($this->errs['message']);
        }
    }

    public function updatedRecaptcha()
    {
        if (! empty($this->errs['recaptcha'])) {
            unset($this->errs['recaptcha']);
        }
    }

    public function onSend()
    {
        $data = [
            'full_name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'recaptcha' => $this->recaptcha,
        ];

        $validator = Validator::make($data, [
            'full_name' => 'required|min:4|max:120',
            'email' => 'required|email:rfc,dns',
            'subject' => 'required|min:15|max:180',
            'message' => 'required|min:15|max:255',
            'recaptcha' => 'required',
        ]);

        $validator->setCustomMessages([
            'recaptcha.required' => 'The reCAPTCHA must be checked.',
        ]);

        if ($validator->fails()) {
            $this->errs = json_decode(json_encode($validator->errors()->toArray()), true);
        } else {
            $agent = new Agent;

            $event = [
                'token' => $this->recaptcha,
                'siteKey' => config('services.recaptcha.site_key'),
                'userAgent' => $agent->getUserAgent(),
            ];

            $response = Http::post(
                'https://recaptchaenterprise.googleapis.com/v1/projects/'
                .config('services.recaptcha.project_id')
                .'/assessments?key='
                .config('services.recaptcha.api_key'),
                ['event' => $event]
            );

            if ($response->failed()) {
                $this->errs = json_decode(json_encode($validator->errors()->add('recaptcha', 'Something went wrong, please try again later.')->toArray()), true);

                return;
            }

            $res_json = json_decode($response->body());
            $risk_analysis = $res_json->riskAnalysis;

            if ($risk_analysis->score < 0.8) {
                $this->errs = json_decode(json_encode($validator->errors()->add('recaptcha', 'Google Recaptcha verification failed. Please try again.')->toArray()), true);

                return;
            }

            $inquiry = new Inquiry;
            $inquiry->full_name = $data['full_name'];
            $inquiry->email = $data['email'];
            $inquiry->subject = $data['subject'];
            $inquiry->message = $data['message'];

            $inquiry->ticket_no = $this->generate_ticket_number('inquiry');
            DB::beginTransaction();
            try {
                $inquiry->save();
                $this->sendMail('solutions@repay.ph', new RepayMail('INQUIRY', [
                    'Inquiry from : '.$this->name.' ('.$this->email.')',
                    '<br />',
                    'Subject: '.$this->subject,
                    '<br />',
                    'Message:',
                    '<br />',
                    $this->message,
                ]));
                $this->apiSuccessMsg = 'Thank you for reaching out to us. Please expect an email from us within 48 hours.';

                Mail::to($data['email'])->send(new InquirySuccess($data['full_name'], $data['email']));
                DB::commit();
            } catch (Exception $ex) {
                DB::rollBack();
                Log::error('ContactUs.onSend: ' . $ex->getMessage());
                return $this->apiErrorMsg = 'Something went wrong, please try again later.';
            }

            $this->reset(['name', 'email', 'subject', 'message', 'recaptcha']);
        }
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('guest.contact-us.contact-us');
    }
}
