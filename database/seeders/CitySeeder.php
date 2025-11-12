<?php

namespace Database\Seeders;

use App\Enums\CityEnum;
use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            ['name' => 'Ahmadpur East', 'code' => 'APE'],
            ['name' => 'Ahmed Nager Chatha', 'code' => 'ANC'],
            ['name' => 'Ali Khan Abad', 'code' => 'AKA'],
            ['name' => 'Alipur', 'code' => 'ALP'],
            ['name' => 'Arifwala', 'code' => 'ARF'],
            ['name' => 'Attock', 'code' => 'ATK'],
            ['name' => 'Bahawalnagar', 'code' => 'BWN'],
            ['name' => 'Bahawalpur', 'code' => 'BWP'],
            ['name' => 'Bhalwal', 'code' => 'BHL'],
            ['name' => 'Bhakkar', 'code' => 'BKR'],
            ['name' => 'Burewala', 'code' => 'BRW'],
            ['name' => 'Chakwal', 'code' => 'CHW'],
            ['name' => 'Chiniot', 'code' => 'CHT'],
            ['name' => 'Chishtian', 'code' => 'CHS'],
            ['name' => 'Chunian', 'code' => 'CHN'],
            ['name' => 'Daska', 'code' => 'DSK'],
            ['name' => 'Dera Ghazi Khan', 'code' => 'DGK'],
            ['name' => 'Dina', 'code' => 'DIA'],
            ['name' => 'Dipalpur', 'code' => 'DPL'],
            ['name' => 'Faisalabad', 'code' => 'FSD'],
            ['name' => 'Gojra', 'code' => 'GOJ'],
            ['name' => 'Gujranwala', 'code' => 'GUJ'],
            ['name' => 'Gujrat', 'code' => 'GRT'],
            ['name' => 'Hafizabad', 'code' => 'HFZ'],
            ['name' => 'Haroonabad', 'code' => 'HRN'],
            ['name' => 'Hasilpur', 'code' => 'HSP'],
            ['name' => 'Islamabad', 'code' => 'ISB'],
            ['name' => 'Jalalpur Jattan', 'code' => 'JPJ'],
            ['name' => 'Jampur', 'code' => 'JPR'],
            ['name' => 'Jaranwala', 'code' => 'JRW'],
            ['name' => 'Jhang', 'code' => 'JHG'],
            ['name' => 'Jhelum', 'code' => 'JLM'],
            ['name' => 'Kasur', 'code' => 'KSR'],
            ['name' => 'Kamalia', 'code' => 'KML'],
            ['name' => 'Kamoke', 'code' => 'KMK'],
            ['name' => 'Khanewal', 'code' => 'KNL'],
            ['name' => 'Khanpur', 'code' => 'KPR'],
            ['name' => 'Khushab', 'code' => 'KSB'],
            ['name' => 'Kot Adu', 'code' => 'KAD'],
            ['name' => 'Lahore', 'code' => 'LHE'],
            ['name' => 'Layyah', 'code' => 'LYH'],
            ['name' => 'Lodhran', 'code' => 'LDN'],
            ['name' => 'Mailsi', 'code' => 'MLS'],
            ['name' => 'Mandi Bahauddin', 'code' => 'MBN'],
            ['name' => 'Mian Channu', 'code' => 'MCN'],
            ['name' => 'Mianwali', 'code' => 'MWI'],
            ['name' => 'Multan', 'code' => 'MUX'],
            ['name' => 'Murree', 'code' => 'MRE'],
            ['name' => 'Muridke', 'code' => 'MRD'],
            ['name' => 'Muzaffargarh', 'code' => 'MZG'],
            ['name' => 'Narowal', 'code' => 'NWL'],
            ['name' => 'Nankana Sahib', 'code' => 'NKS'],
            ['name' => 'Okara', 'code' => 'OKR'],
            ['name' => 'Pakpattan', 'code' => 'PKP'],
            ['name' => 'Pattoki', 'code' => 'PTK'],
            ['name' => 'Pindi Bhattian', 'code' => 'PBN'],
            ['name' => 'Pind Dadan Khan', 'code' => 'PDK'],
            ['name' => 'Qila Didar Singh', 'code' => 'QDS'],
            ['name' => 'Rahim Yar Khan', 'code' => 'RYK'],
            ['name' => 'Rajanpur', 'code' => 'RJP'],
            ['name' => 'Rawalpindi', 'code' => 'RWP'],
            ['name' => 'Sadiqabad', 'code' => 'SDQ'],
            ['name' => 'Sahiwal', 'code' => 'SWL'],
            ['name' => 'Samundri', 'code' => 'SMD'],
            ['name' => 'Sargodha', 'code' => 'SGD'],
            ['name' => 'Sheikhupura', 'code' => 'SKP'],
            ['name' => 'Shujaabad', 'code' => 'SJB'],
            ['name' => 'Sialkot', 'code' => 'SKT'],
            ['name' => 'Talagang', 'code' => 'TLG'],
            ['name' => 'Taxila', 'code' => 'TXL'],
            ['name' => 'Toba Tek Singh', 'code' => 'TTS'],
            ['name' => 'Vehari', 'code' => 'VHR'],
            ['name' => 'Wah Cantonment', 'code' => 'WAH'],
            ['name' => 'Wazirabad', 'code' => 'WZD'],
            ['name' => 'Zafarwal', 'code' => 'ZFW'],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(
                ['name' => $city['name']],
                [
                    'code' => strtoupper($city['code']),
                    'status' => CityEnum::ACTIVE->value,
                ]
            );
        }
    }
}
