<?php

namespace App\Http\Controllers;

use App\Http\Resources\SlidersResource;
use App\Models\Sliders;
use App\Traits\HandleResponse;
use Illuminate\Support\Facades\Cache;

class SlidersController extends Controller
{
    use HandleResponse;
    public function index($id)
    {
        try {
            $sliders = Cache::rememberForever('slider_' . $id, function () use ($id) {
                return Sliders::find($id);
            });
            $allSliders = new SlidersResource($sliders);
            return $this->successData($allSliders);
        } catch (\Exception $e) {
            return $this->error();
        }
    }
}
