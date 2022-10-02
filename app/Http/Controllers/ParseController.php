<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ParseController extends Controller
{
    public function toNumber(Request $request)
    {
        $words = $request->input('words');
        //        $fuzzyResult = $this->fuzzy($words);
        //        if ($fuzzyResult['error']) {
        //            return customResponse()
        //                ->data([
        //                    'type' => 'typo_words',
        //                    'suggestion' => $fuzzyResult['suggestion'],
        //                ])
        //                ->message("Do you mean {$fuzzyResult['suggestion']}?")
        //                ->failed()
        //                ->generate();
        //        }
        $number = $this->wordsToNumbers($words);
        if ($number <= 0) {
            return customResponse()
                ->data(['type' => 'invalid_words'])
                ->message("{$words} is not valid word for number translation.")
                ->failed()
                ->generate();
        }

        return customResponse()
            ->data($number)
            ->message('Words to Numbers successful.')
            ->success()
            ->generate();
    }

    public function toWords(Request $request)
    {
        $numbers = $request->input('numbers');
        $words = $this->numbersToWords($numbers);
        if (empty($words)) {
            return customResponse()
                ->data([])
                ->message("{$numbers} is not valid numbers for words translation.")
                ->failed()
                ->generate();
        }

        return customResponse()
            ->data($words)
            ->message('Numbers to Words successful.')
            ->success()
            ->generate();
    }

    private function fuzzy($words)
    {
        $curl = curl_init();
        $words = ucfirst(strtolower($words));
        $postFieldText = join('%20', explode(' ', $words));
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://dnaber-languagetool.p.rapidapi.com/v2/check',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "language=en-US&text={$postFieldText}",
            CURLOPT_HTTPHEADER => [
                'X-RapidAPI-Host: dnaber-languagetool.p.rapidapi.com',
                'X-RapidAPI-Key: ' . env('RAPID_API_KEY'),
                'content-type: application/x-www-form-urlencoded',
            ],
        ]);
        $responseJSON = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return ['error' => false]; // Skip fuzzy searching
        } else {
            $responseObj = json_decode($responseJSON, true);
            if (count($responseObj['matches']) === 0) {
                return ['error' => false]; // Skip fuzzy searching
            }
            $match = $responseObj['matches'][0];
            $replacement = $match['replacements'][0];
            $context = $match['context'];
            $toReplace = join(
                '',
                array_slice(str_split($words), $context['offset'], $context['length'])
            );
            $suggestion = str_replace($toReplace, $replacement['value'], $words);
            if (empty($suggestion)) {
                return ['error' => true, 'suggestion' => $words];
            }

            return ['error' => true, 'suggestion' => strtoupper($suggestion)];
        }
    }

    private function wordsToNumbers($words)
    {
        $words = strtolower($words);
        $numberData = $this->getNumberData('constants');
        $words = strtr(
            $words,
            $numberData['unit'] + $numberData['ten'] + $numberData['extension']
        );
        $collection = [];
        $total = 0;
        $behind = null;
        $numbers = preg_split('/[\s-]+/', $words);
        for ($index = 0; $index <= count($numbers) - 1; $index++) {
            $number = floatval($numbers[$index]);
            if (empty($collection)) {
                array_push($collection, $number);
                $behind = $number;
                continue;
            }
            if (end($collection) < $number) {
                array_push($collection, array_pop($collection) * $number);
                $behind = $number;
                continue;
            }
            if ($behind < 1000) {
                array_push($collection, array_pop($collection) + $number);
                $behind = $number;
                continue;
            }
            $total += array_pop($collection);
            array_push($collection, $number);
            $behind = $number;
        }

        return $total + array_pop($collection);
    }

    private function numbersToWords($number)
    {
        $number = str_replace([',', ''], '', trim($number));
        $number = (int) $number;
        $words = [];
        $numberData = $this->getNumberData();
        $units = $numberData['unit'];
        $tenths = $numberData['ten'];
        $numLength = strlen($number);
        $steps = (int) (($numLength + 2) / 3);
        $max_length = $steps * 3;
        $number = substr('00' . $number, -$max_length);
        $num_levels = str_split($number, 3);
        if ($steps >= 5) {
            return null;
        }
        for ($index = 0; $index < count($num_levels); $index++) {
            $steps--;
            $hundred = (int) ($num_levels[$index] / 100);
            $hundred = $hundred ? array_search($hundred, $units) . ' hundred' : '';
            $tenth = (int) ($num_levels[$index] % 100);
            $single = '';
            if ($tenth < 20) {
                $tenth = $tenth ? ' and ' . array_search($tenth, $units) . ' ' : '';
            } else {
                $tenth = (int) ($tenth / 10);
                $tenth = ' and ' . array_search(($tenth / 10) * 100, $tenths);
                $single = (int) ($num_levels[$index] % 10);
                if ($single > 0) {
                    $single = ' ' . array_search($single, $units) . ' ';
                } else {
                    $single = '';
                }
            }
            $extension = '';
            if ($steps && (int) $num_levels[$index]) {
                $extension = match ($steps) {
                    1 => 'thousand',
                    2 => 'million',
                    3 => 'billion',
                };
            }
            $words[] = $hundred . $tenth . $single . $extension;
        }
        $words = preg_replace('/^\s\b(and)/', '', join(' ', $words));

        return $words;
    }

    private function getNumberData()
    {
        return [
            'unit' => config('constants.UNIT'),
            'ten' => config('constants.TEN'),
            'extension' => config('constants.EXTENSION'),
        ];
    }
}
