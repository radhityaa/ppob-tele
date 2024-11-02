<?php

namespace App\Http\Controllers;

use App\Models\Digiflazz;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function priceListPrabayar()
    {
        return view('price-list.prabayar');
    }

    public function getProvider(Request $request)
    {
        if (!$request->isMethod('GET') || !$request->ajax()) {
            return abort(404, 'Not Found');
        }

        $data = Digiflazz::where('category', $request->service)->select('brand')->groupBy('brand')->get();
        return response()->json($data);
    }

    public function getType(Request $request)
    {
        if (!$request->isMethod('GET') || !$request->ajax()) {
            return abort(404, 'Not Found');
        }

        $data = Digiflazz::where('category', $request->service)->where('brand', $request->provider)->select('type')->groupBy('type')->get();
        return response()->json($data);
    }

    public function getServices(Request $request)
    {
        if (!$request->isMethod('GET') || !$request->ajax()) {
            return abort(404, 'Not Found');
        }

        $data = Digiflazz::where('category', $request->service)->where('brand', $request->provider)->where('type', $request->category)->orderBy('price', 'desc')->get();
        return response()->json($data);
    }
}
