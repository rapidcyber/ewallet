<?php

namespace App\User\Link;

use App\Models\LinkedRealholmesAccount;
use Auth;
use Http;
use Livewire\Attributes\Url;
use Illuminate\Support\Str;
use Livewire\Component;
use Request;

class Realholmes extends Component
{

    #[Url]
    public $error = '';

    #[Url]
    public $error_description = '';

    #[Url]
    public $code = '';

    #[Url]
    public $role = '';

    public function mount()
    {
        if (empty($this->error) == false) {
            return abort( 403, $this->error_description);
        }

        if (empty($this->code)) {
            $user = auth('api')->user();
            if (empty($user)) {
                $user = auth()->user();
            }
    
            if (empty($user)) {
                return redirect()->route('home');
            } else {
                Request::session()->put('user_id', $user->id);
            }

            if (empty($this->role)) {
                return abort(403);
            }
            Request::session()->put('role', $this->role);

            $state = Str::random(40);
            $code_verifier = Str::random(128);
            $codeChallenge = strtr(rtrim($code_verifier, '='), '+/', '-_');

            Request::session()->put('state', $state);
            Request::session()->put('code_verifier', $code_verifier);

            $codeChallenge = strtr(rtrim(
                base64_encode(hash('sha256', $code_verifier, true)),
                '='
            ), '+/', '-_');

            $query = http_build_query([
                'client_id' => config('services.realholmes.client_id'),
                'redirect_uri' => config('services.realholmes.callback_uri'),
                'response_type' => 'code',
                'scope' => '',
                'state' => $state,
                'prompt' => 'consent',
                'role' => $this->role,
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => 'S256',
            ]);
            return redirect(config('services.realholmes.url') . '/oauth/authorize?' . $query);
        } else {
            $user  = Auth::loginUsingId(Request::session()->pull('user_id'));

            //// Check session and request state equality
            $session_state = Request::session()->pull('state');
            $request_state = Request::get('state');
            if ($session_state !== $request_state) {
                return abort(404);
            }

            $code_verifier = Request::session()->pull(key: 'code_verifier');
            $data = [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.realholmes.client_id'),
                'redirect_uri' => config('services.realholmes.callback_uri'),
                'code_verifier' => $code_verifier,
                'code' => $this->code,
            ];

            $response = Http::asForm()->post(config('services.realholmes.url') . '/oauth/token', $data);
            if ($response->failed()) {
                return abort(403);
            }
            $response_json = json_decode($response->body());

            $user = auth()->user();
            $acc = LinkedRealholmesAccount::firstOrNew([
                'entity_id' => $user->id,
                'entity_type' => get_class($user),
            ]);
            $acc->role = Request::session()->pull('role');
            $acc->control = Str::orderedUuid();
            $acc->token = $response_json->access_token;
            $acc->refresh = $response_json->refresh_token;
            $acc->save();
            return redirect(route('link.status') . '?token=' . $acc->token);
        }
    }

    public function render()
    {
        return view('user.link.realholmes');
    }
}
