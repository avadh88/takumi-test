<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    /**
     * The var implementation.
     *
     * @var string $currency
     * @var int $discountThirty,$discountFifteen
     */
    protected $discountThirty = 30;
    protected $discountFifteen = 15;
    protected $currency = "EUR";

    /**
     * Show product list
     * 
     * @return \Illuminate\Http\Request
     * @return \Illuminate\Http\JsonResponse
     */
    public function products(Request $request)
    {
        $filename = 'products';
        $path = storage_path() . "/json/${filename}.json";
        $json = json_decode(file_get_contents($path), true);
        $category = $request->category;
        $price = $request->price;

        if ($request->price && $request->category) {
            $finalArray = array_filter($json['products'], function ($var) use ($category, $price) {
                if ($var['price'] == $price && $var['category'] == $category) {
                    return $var;
                }
            });
        } else if ($request->category) {
            $finalArray = array_filter($json['products'], function ($var) use ($category) {
                if ($var['category'] == $category) {
                    return $var;
                }
            });
        } else if ($request->price) {
            $finalArray = array_filter($json['products'], function ($var) use ($price) {
                if ($var['price'] == $price) {
                    return $var;
                }
            });
        } else {
            $finalArray = $json['products'];
        }

        foreach ($finalArray as $key => $data) {
            if ($data['category'] === 'insurance') {

                $price = (int)$data['price'];
                $dicountedPrice = ($price * $this->discountThirty) / 100;
                $finalPrice = $price - $dicountedPrice;

                $finalArray[$key]['sku'] = $data['sku'];
                $finalArray[$key]['name'] = $data['name'];
                $finalArray[$key]['category'] = $data['category'];

                $finalArray[$key]['price'] = [
                    "original" => $price,
                    "final" => $finalPrice,
                    "discount_percentage" => $this->discountThirty . '%',
                    "currency" => "EUR"
                ];
            }

            if ($data['sku'] === '000003') {

                $price = (int)$data['price'];
                $dicountedPrice = ($price * $this->discountFifteen) / 100;
                $finalPrice = $price - $dicountedPrice;


                $finalArray[$key]['sku'] = $data['sku'];
                $finalArray[$key]['name'] = $data['name'];
                $finalArray[$key]['category'] = $data['category'];
                $finalArray[$key]['price'] = [
                    "original" => $price,
                    "final" => $finalPrice,
                    "discount_percentage" => $this->discountFifteen . '%',
                    "currency" => "EUR"
                ];
            }
            if ($data['sku'] !== '000003' && $data['category'] !== 'insurance') {
                $price = (int)$data['price'];

                $finalArray[$key]['sku'] = $data['sku'];
                $finalArray[$key]['name'] = $data['name'];
                $finalArray[$key]['category'] = $data['category'];
                $finalArray[$key]['price'] = [
                    "original" => $price,
                    "final" => $price,
                    "discount_percentage" => null,
                    "currency" => "EUR"
                ];
            }
        }

        $response['status'] = true;
        $response['data'] = array_values($finalArray);

        return response()->json($response);
    }
}