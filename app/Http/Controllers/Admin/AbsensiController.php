<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Presensi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AbsensiController extends Controller
{
    public function create()
    {
        $check = Presensi::where('tgl_presensi', date('Y-m-d'))->where('user_id', Auth::user()->id)->count();

        return view('admin.presensi.create', compact('check'));
    }

    public function store(Request $request)
    {
        $user_id = Auth::user()->id;
        $tgl_presensi = date("Y-m-d");
        $jam = date("H:i:s");
        $latitude_kator = 2.965847; // sesuaikan dengan radius di google map atas posisi kantor
        $longitude_kator = 99.0625; // dan jangan lupa sesuaikan latitude & longitude yang disisini dengan di halaman view create yang di script js nya, agar sama.
        $lokasi = $request->lokasi;
        $lokasi_user = explode(',', $lokasi);
        $latitued_user = $lokasi_user[0];
        $longitud_user = $lokasi_user[1];

        $jarak = $this->distance($latitude_kator, $longitude_kator, $latitued_user, $longitud_user);
        $radius = round($jarak['meters']);
        // dd($radius);
        $image = $request->image;
        $folderPath = 'public/presensi/';
        $formatName = $user_id . '-' . $tgl_presensi;
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName . '.' . "png";
        $file = $folderPath . $fileName;

        $check = Presensi::where('tgl_presensi', date('Y-m-d'))->where('user_id', Auth::user()->id)->count();

        // ngecek jarak, jika jarak lebih dari 10 meter maka tampilin error
        // dan jangan lupa atur juga jarak di halaman view nya menjadi 10 meter
        if ($radius > 10) {
            echo 'Error|Maaf Anda Berada Diluar Radius|';
        } else {
            if ($check > 0) {
                $data_pulang = [
                    'jam_out' => $jam,
                    'foto_out' => $fileName,
                    'location_out' => $lokasi,
                ];
                $update = Presensi::where('tgl_presensi', date('Y-m-d'))->where('user_id', Auth::user()->id)->update($data_pulang);
    
                if ($update) {
                    Storage::put($file, $image_base64);
                    echo 'success|Terimakasih, Hati Hati di Jalan|out';
                } else {
                    echo 'error|Maaf gagal absen, Hubungi tim IT|out';
                }
            } else {
                $data_masuk = Presensi::create([
                    'user_id' => $user_id,
                    'tgl_presensi' => $tgl_presensi,
                    'jam_in' => $jam,
                    'foto_in' => $fileName,
                    'location_in' => $lokasi,
                ]);
    
                if ($data_masuk) {
                    echo 'success|Terimakasih, Selamat Bekerja|in';
                    Storage::put($file, $image_base64);
                } else {
                    echo 'error|Maaf gagal absen, Hubungi tim IT|in';
                }
            }
        }

        // if ($absensi) {
        //     Storage::put($file, $image_base64);
        //     echo 0;
        // } else {
        //     echo 1;
        // }
    }

    // funsi ini untuk menghitung jarak dari lokasi user
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2))* cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilomeneters = $miles * 1.609344;
        $meters = $kilomeneters * 1000;
        return compact('meters');
        // return ($miles * 1.609344);
    }
}
