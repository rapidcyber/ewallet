<?php

namespace App\Traits;

use Illuminate\Support\Str;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A128CBCHS256;
use Jose\Component\Encryption\Algorithm\KeyEncryption\RSAOAEP;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\Serializer\CompactSerializer as SerializerCompactSerializer;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;

trait WithBPIFunctions
{
    public function create_jwe()
    {
        $jws = $this->create_jws();

        $public_key = file_get_contents(storage_path('/bpi/jwt-public.pub')); /// BPI PUB KEY

        $algorithmManager = new AlgorithmManager([
            new RSAOAEP(),
            new A128CBCHS256()
        ]);

        $jweBuilder = new JWEBuilder($algorithmManager);

        $jwk = JWKFactory::createFromKey($public_key);

        $jwe = $jweBuilder
            ->create()
            ->withPayload($jws)
            ->withSharedProtectedHeader([
                'alg' => 'RSA-OAEP',
                'enc' => 'A128CBC-HS256'
            ])
            ->addRecipient($jwk)
            ->build();

        $serializer = new SerializerCompactSerializer();
        $jwe = $serializer->serialize($jwe);

        return $jwe;
    }

    private function create_jws()
    {
        $private_key = config('app.private_key'); /// REPAY PRIV KEY

        $algorithmManager = new AlgorithmManager([
            new RS256(),
        ]);

        $jwk = JWKFactory::createFromKey($private_key);

        $jwsBuilder = new JWSBuilder($algorithmManager);

        $id = Str::uuid();
        $exp = now()->addMinutes(20)->unix();

        $payload = json_encode([
            'jti' => $id,
            'iss' => "PARTNER",
            'aud' => "BPI",
            "sub" => "recurringDebits recurringDebitsDeLink",
            "exp" => $exp,
        ]);

        $jws = $jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature($jwk, ['alg' => 'RS256'])
            ->build();

        $serializer = new CompactSerializer();
        $jws = $serializer->serialize($jws);

        return $jws;
    }

    public function validate_jwt($token)
    {
        $algorithmManager = new AlgorithmManager([
            new RSAOAEP(),
            new A128CBCHS256()
        ]);

        $jweDecrypter = new JWEDecrypter($algorithmManager);

        $private_key = config('app.private_key'); /// REPAY PRIV KEY

        $jwk = JWKFactory::createFromKey($private_key);

        $serializeManager = new JWESerializerManager([
            new SerializerCompactSerializer(),
        ]);

        $jwe = $serializeManager->unserialize($token);

        return $jweDecrypter->decryptUsingKey($jwe, $jwk, 0);
    }
}
