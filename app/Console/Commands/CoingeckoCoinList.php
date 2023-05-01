<?php

namespace App\Console\Commands;

use App\Models\Coin;
use App\Models\Platform;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class CoingeckoCoinList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coin:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will get Coingecko coin list';


    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle(): void
    {
        $message = '';
        $url = "https://api.coingecko.com/api/v3/coins/list?include_platform=true";
        try {
            DB::beginTransaction();
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            curl_close($curl);
            $coinsToInsert = $platformToInsert = [];
            if ($response != false) {
                $coinList = json_decode($response, true);
                /**
                 * Sometimes the api call limit exceeds the limit for free plan.
                 * So we use the empty($coinList['status']) to check that there is no error message for limit exceeding.
                 * If there is any error message for limit of free plan then it will go to the else part and there we get the error message from coingecko api.
                 */
                if (empty($coinList['status'])) {
                    foreach ($coinList as $key => $row) {
                        $coinsToInsert[] = [
                            'coin_id' => $row['id'],
                            'name' => mb_convert_encoding($row['name'], 'UTF-8', 'UTF-8'),
                            'symbol' => mb_convert_encoding($row['symbol'], 'UTF-8', 'UTF-8'),
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                        if (!empty($row['platforms'])) {
                            foreach ($row['platforms'] as $ind => $value) {
                                if (!empty($value)) {
                                    $platformToInsert[] = [
                                        'coin_id' => $row['id'],
                                        'name' => $ind,
                                        'value' => $value,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => Carbon::now(),
                                    ];
                                }
                            }
                        }
                    }
                    Coin::upsert($coinsToInsert, ['coin_id'], ['name', 'symbol', 'created_at', 'updated_at']);
                    Platform::upsert($platformToInsert, ['value'], ['name', 'value', 'created_at', 'updated_at']);
                    $message = 'Data is saved successfully';
                    DB::commit();
                } else {
                    if (!empty($coinList['status'])) {
                        $message = $coinList['status']['error_message']; // Here we get the error message form coingecko api.
                    }
                }
            } else {
                $error = curl_error($curl);
                curl_close($curl);
                $message = $error;
                Log::error('Failed to save. Error: ' . $error);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            $message = $th->getMessage();
            Log::error('Failed to save. Error: ' . $th->getMessage());
        }

        echo $message;
        exit();
    }
}
