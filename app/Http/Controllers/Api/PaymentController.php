<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Token;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    public function createToken(Request $request)
    {
        // Establecer la clave secreta de Stripe (solo para pruebas)
        Stripe::setApiKey(config('stripe.secret'));

        // Obtener los datos de la tarjeta desde el cuerpo de la solicitud JSON
        $cardData = $request->input('card'); // Suponiendo que recibes la tarjeta como un objeto

        try {
            // Crear el token de Stripe usando los datos de la tarjeta
            $token = Token::create([
                'card' => $cardData,
            ]);

            // Retornar el token generado
            return response()->json([
                'status' => 'success',
                'token' => $token->id, // Retornar solo el token generado
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
