<?php

namespace App\Http\Controllers;

use App\Model\ApiToken;
use App\Model\CallbackUrl;
use Illuminate\Http\Request;

class ApiSpecificationController extends Controller
{
    public function index()
    {
        $tokens = ApiToken::all();
        $callback = CallbackUrl::first();
        return view('apiSpecification.apiSpecification', compact('tokens', 'callback'));
    }

    public function storeToken(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
        ]);

        ApiToken::create([
            'ip' => $request->ip,
            'token' => bin2hex(random_bytes(16)),
            'status' => 'In-Active',
        ]);

        return redirect()->back()->with('success', 'Token added successfully');
    }

    public function updateCallback(Request $request)
    {
        $request->validate([
            'payin_callback' => 'required|url',
            'payout_callback' => 'required|url',
        ]);

        $callback = CallbackUrl::first();
        if ($callback) {
            $callback->update($request->all());
        } else {
            CallbackUrl::create($request->all());
        }

        return redirect()->back()->with('success', 'Callback URLs updated successfully');
    }

    public function toggleTokenStatus($id)
    {
        $token = ApiToken::findOrFail($id);
        $token->status = $token->status === 'Active' ? 'In-Active' : 'Active';
        $token->save();

        return redirect()->back()->with('success', 'Token status updated');
    }
}