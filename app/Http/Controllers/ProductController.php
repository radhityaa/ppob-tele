<?php

namespace App\Http\Controllers;

use App\Models\Digiflazz;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function list()
    {
        return view('price-list');
    }

    public function getProvider(Request $request)
    {
        $data = Digiflazz::where('category', $request->service)->select('brand')->groupBy('brand')->get();
        return response()->json($data);
    }

    public function getType(Request $request)
    {
        $data = Digiflazz::where('category', $request->service)->where('brand', $request->provider)->select('type')->groupBy('type')->get();
        return response()->json($data);
    }

    public function getServices(Request $request)
    {
        $data = Digiflazz::where('category', $request->service)->where('brand', $request->provider)->where('type', $request->category)->get();
        return response()->json($data);
    }
}
