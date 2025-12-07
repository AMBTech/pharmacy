<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductBatch;

class InventoryController extends Controller
{
    public function index()
    {

    }

    public function getProductBatches($product)
    {
        return ProductBatch::query()->where('product_id', $product)->get();
    }
}
