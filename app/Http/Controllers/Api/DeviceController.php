<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeviceController extends ApiController
{
    public function updateToken(Request $request)
    {
        $validated = $request->validate([
            'device_token' => 'required|string'
        ]);

        $user = $request->user();
        $user->update([
            'device_token' => $validated['device_token']
        ]);

        return $this->respondSuccess(null, 'Device token updated successfully');
    }

    public function removeToken(Request $request)
    {
        $request->user()->update([
            'device_token' => null
        ]);

        return $this->respondSuccess(null, 'Device token removed successfully');
    }
} 