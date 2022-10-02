<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConversionController extends Controller
{
    public function toUSD(Request $request)
    {
        $peso = $request->input('peso');
        $exchangeRateData = $this->getExchangeRateData($peso);
        if ($exchangeRateData['error']) {
            return customResponse()
                ->data([])
                ->message('Something went wrong in the server.')
                ->failed()
                ->generate();
        }
        $usd = $exchangeRateData['conversion'];

        return customResponse()
            ->data($usd)
            ->message('PHP conversion to USD successful.')
            ->success()
            ->generate();
    }

    private function getExchangeRateData($peso)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.exchangerate-api.com/v4/latest/USD',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        $responseJSON = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return ['error' => true];
        } else {
            $responseObj = json_decode($responseJSON, true);
            $phpRate = $responseObj['rates']['PHP'];
            $conversion = $peso / $phpRate;

            return ['error' => false, 'conversion' => $conversion];
        }
    }
}
