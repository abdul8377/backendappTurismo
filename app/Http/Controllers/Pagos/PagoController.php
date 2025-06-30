<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;

class PagoController extends Controller
{
    public function procesarPago(Request $request)
    {
        Stripe::setApiKey(config('stripe.secret'));

        try {
            $charge = Charge::create([
                'amount' => $request->amount, // monto en centavos
                'currency' => 'usd',
                'source' => $request->token, // obtenido del frontend
                'description' => 'Compra en appTurismo',
            ]);

            return response()->json(['status' => 'success', 'charge' => $charge]);
        } catch (ApiErrorException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
