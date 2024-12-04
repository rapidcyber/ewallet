<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use SimpleXMLElement;
use Illuminate\Http\Client\Response;

trait WithAllBankFunctions
{

    /**
     * Summary of generate_token
     * @return string[]
     */
    private function generate_token()
    {
        $access_id = config('services.alb.api_id');
        $secret_key = config('services.alb.api_secret');
        $transaction_date = now()->format('Y-m-d\TH:i:s.000P');
        $token = hash('sha1', $access_id . $secret_key . $transaction_date);

        return [strtoupper($token), $transaction_date];
    }

    /**
     * Summary of generate_xml_string
     * @param array $arr
     * @param string $tag
     * @return array|string
     */
    private function generate_xml_string(array $arr, string $tag = "Account.Info")
    {
        $xml = new SimpleXMLElement("<$tag/>");

        foreach ($arr as $key => $val) {
            $xml->addAttribute($key, $val);
        }

        $xml = str_replace('<?xml version="1.0"?>', '', $xml->asXML());
        $xml = str_replace("\n", '', $xml);
        return $xml;
    }

    /**
     * Summary of get_xml_contents
     * @param \Illuminate\Http\Client\Response $response
     * @param bool $first_attr
     * @return mixed
     */
    private function get_xml_contents(Response $response, bool $first_attr = true)
    {
        $xml = simplexml_load_string($response->body());
        $json_string = json_encode($xml);
        $json = json_decode($json_string, true);

        if ($first_attr) {
            return $json['@attributes'];
        }

        return $json;
    }

    /**
     * Summary of handle_post
     * @param string $xml_str
     * @return Response
     */
    private function handle_post(string $xml_str)
    {
        return Http::withHeaders([
            'Content-Type' => 'text/xml',
            'SoapAction' => 'http://tempuri.org/iWebInterface/wb_Get_Info',
        ])->send(
                'POST',
                config('services.alb.api_host'),
                ['body' => $xml_str]
            );
    }
}
