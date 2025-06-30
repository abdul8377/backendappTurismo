<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Exception\ApiErrorException;

class PagoController extends Controller
{
    // Procesar pago
    public function procesarPago(Request $request)
    {
        // Establecer la clave secreta de Stripe
        Stripe::setApiKey(config('stripe.secret'));

        try {
            // Crear el cargo con los datos enviados desde el frontend
            $charge = Charge::create([
                'amount' => $request->amount,  // monto en centavos
                'currency' => 'usd',
                'source' => $request->token,   // token obtenido del frontend
                'description' => 'Compra en appTurismo',
            ]);

            return response()->json(['status' => 'success', 'charge' => $charge]);
        } catch (ApiErrorException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    // Mostrar el formulario de pago
    public function mostrarFormulario()
    {
        return view('pago.formulario');
    }
}
