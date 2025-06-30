<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Pago</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            display: block;
        }

        input[type="number"], #card-element {
            width: 100%;
            padding: 10px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        input[type="number"]:focus, #card-element:focus {
            border-color: #4CAF50;
            outline: none;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
        }

        button:hover {
            background-color: #45a049;
        }

        #card-errors {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Formulario de Pago</h1>

        <form id="payment-form">
            <div class="form-group">
                <label for="amount">Monto (USD):</label>
                <input type="number" id="amount" name="amount" placeholder="Ingrese el monto" min="1" required>
            </div>

            <div class="form-group">
                <label for="card-element">Detalles de la tarjeta:</label>
                <div id="card-element">
                    <!-- Elemento de Stripe para la tarjeta -->
                </div>
                <div id="card-errors" role="alert"></div>
            </div>

            <button type="submit">Pagar</button>
        </form>
    </div>

    <!-- Aquí va el código JavaScript que gestionará el token de Stripe -->
    <script>
        var stripe = Stripe("{{ config('stripe.public') }}"); // Tu clave pública de Stripe
        var elements = stripe.elements();
        var card = elements.create('card');
        card.mount('#card-element');

        var form = document.getElementById('payment-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // Obtener el monto ingresado
            var amount = document.getElementById('amount').value;

            // Validar que el monto sea mayor a 0
            if (amount <= 0) {
                alert('Por favor ingresa un monto válido.');
                return;
            }

            stripe.createToken(card).then(function(result) {
                if (result.error) {
                    // Mostrar el error si ocurre
                    var errorElement = document.getElementById('card-errors');
                    errorElement.textContent = result.error.message;
                } else {
                    // Llama a la función que maneja el token
                    stripeTokenHandler(result.token, amount);
                }
            });
        });

        function stripeTokenHandler(token, amount) {
            var data = {
                token: token.id, // Token de Stripe
                amount: amount * 100 // Monto en centavos (ejemplo: 10 USD)
            };

            // Enviar el token y el monto al backend para procesar el pago
            fetch('/procesar-pago', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Pago exitoso');
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Hubo un problema con el pago.');
            });
        }
    </script>
</body>
</html>
