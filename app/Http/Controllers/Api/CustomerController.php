<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::when(request('search'), fn ($q, $s) => $q->where('nama', 'ilike', "%{$s}%"))
            ->orderBy('nama')
            ->paginate(10);

        return CustomerResource::collection($customers);
    }

    public function show($id)
    {
        $customer = Customer::with('rentals')->findOrFail($id);

        return new CustomerResource($customer);
    }
}