<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TourResource;
use App\Models\Travel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TourController extends Controller
{
    public  function  index(Travel $travel, Request $request)
    {
         $request->validate([
             'priceFrom'    => 'numeric',
             'priceTo'      => 'numeric',
             'dateFrom'     => 'date',
             'dateTo'       => 'date',
             'sortBy'       => Rule::in(['price']),
             'sortOrder'    => Rule::in(['asc' .'desc']),
         ],[
             'sortBy' => "The 'sortBy' parameter accepts only 'price' value",
             'sortOrder' => "The 'sortOrder' parameter accepts only 'asc' pr 'desc' value",
         ]);

          $tours =  $travel->tours()
              ->when($request->priceFrom, function ($query) use ($request) {
                  $query->where('price', '>=', $request->priceFrom * 100);
              })
              ->when($request->priceTo, function ($query) use ($request) {
                  $query->where('price', '<=', $request->priceTo * 100);
              })
              ->when($request->dateFrom, function ($query) use ($request) {
                  $query->where('starting_date', '>=', $request->dateFrom);
              })
              ->when($request->dateTo, function ($query) use ($request) {
                  $query->where('starting_date', '<=', $request->dateTo);
              })
              ->orderBy('starting_date')
              ->paginate();


        return TourResource::collection($tours);
    }
}

//http://travel-api.test/api/v1/travels/some-thing/tours?priceFrom=123&priceTo=456&dateFrom=2023-06-01&dateTo=2023-07-01
//if (!in_array($request->sortOrder, ['asc', 'desc'])) return; //error message
