<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use TCG\Voyager\Models\Post;

class ApiController extends Controller
{
    public static $kasusProvURL = 'https://services5.arcgis.com/VS6HdKS0VfIhv8Ct/arcgis/rest/services/COVID19_Indonesia_per_Provinsi/FeatureServer/0/query?';

    public static $kecamatanURL = 'https://services5.arcgis.com/VS6HdKS0VfIhv8Ct/arcgis/rest/services/Kecamatan_Rawan_COVID19/FeatureServer/0/query?';

    public static $rsRujukanURL = 'https://services5.arcgis.com/VS6HdKS0VfIhv8Ct/arcgis/rest/services/RS_Rujukan_Update_May_2020/FeatureServer/0/query?';

    public static $statistikURL = 'https://services5.arcgis.com/VS6HdKS0VfIhv8Ct/arcgis/rest/services/Statistik_Perkembangan_COVID19_Indonesia/FeatureServer/0/query?';

    public static $defaultApiQuery = 'where=1%3D1&outFields=*&outSR=4326&f=json';

    public function kasus(Request $request)
    {
        $query = str_replace($request->url(), '', $request->fullUrl());
        if (empty($query)) {
            $query = 'where=1%3D1&outFields=*&outSR=4326&f=geojson';
        }
        $response = Http::get(self::$kasusProvURL . $query);

        return response($response);
    }

    public function kecamatanRawan(Request $request)
    {
        $query = str_replace($request->url(), '', $request->fullUrl());
        if (empty($query)) {
            $query = "where=provinsi = 'jawa barat'&outFields=*&outSR=4326&f=geojson";
        }
        $response = Http::get(self::$kecamatanURL . $query);

        return response($response);
    }

    public function rsRujukan(Request $request)
    {
        $query = str_replace($request->url(), '', $request->fullUrl());
        if (empty($query)) {
            $query = "where=wilayah like '%jawa barat%'&outFields=*&outSR=4326&f=geojson";
        }
        $response = Http::get(self::$rsRujukanURL . $query);

        return response($response);
    }

    public function statistik(Request $request)
    {
        $whereStatistik = '1=1';
        if ($request->query('hariIni') == true) {
            $whereStatistik = "Tanggal > CURRENT_TIMESTAMP - INTERVAL '2' DAY AND PDP > 0";
        }

        $defaultQueryStatistik = "where=$whereStatistik&orderByFields=Hari_ke ASC&outFields=*&outSR=4326&f=json";

        $response = Http::get(self::$statistikURL . $defaultQueryStatistik);

        return response($response);
    }

    public function posts(Request $request)
    {
        $posts = null;
        if ($request->query('paginate') == true) {
            $posts = Post::orderBy('created_at', 'DESC')->paginate(6);

            return response()->json($posts);
        }

        $posts = Post::orderBy('created_at', 'DESC')->get();
        return response()->json($posts);
    }

    public function post(Request $request, $slug)
    {
        if ($slug == 'terbaru') {
            $post = Post::latest()->first();
            return response()->json($post);
        }
        $post = Post::where('slug', $slug)->get();

        return response()->json($post);
    }
}
