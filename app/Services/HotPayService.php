<?php

namespace App\Services;

use App\Enums\PaymentMethod;
use Exception;
use Illuminate\Support\Facades\Http;

class HotPayService
{
    private string $secret;

    private string $notificationPassword;

    public function __construct(private readonly PaymentMethod $method)
    {
        $this->secret = $method->secret();
        $this->notificationPassword = $method->notificationPassword();
    }

    public function generatePayment(
        float $amount,
        string $description,
        string $redirectUrl,
        string $orderId,
        ?string $email,
        ?string $personalData,
    ): array {
        $params = [
            'KWOTA' => $amount,
            'NAZWA_USLUGI' => $description,
            'ADRES_WWW' => $redirectUrl,
            'ID_ZAMOWIENIA' => $orderId,
            'SEKRET' => $this->secret,
        ];

        $params['HASH'] = hash('sha256', $this->notificationPassword.';'.implode(';', $params));

        if (isset($email)) {
            $params['EMAIL'] = $email;
        }
        if (isset($personalData)) {
            $params['DANE_OSOBOWE'] = $personalData;
        }
        $params['TYP'] = 'INIT';

        $req = Http::asForm()->post(
            sprintf('https://%s.hotpay.pl', ($this->method === PaymentMethod::Transfer ? 'platnosc' : 'psc')),
            $params
        );

        $res = $req->fluent();

        if ($res->isEmpty()) {
            throw new Exception('HotPay error: invalid notification password');
        }

        if (! $res->STATUS) {
            throw new Exception('HotPay error: '.$res->WIADOMOSC);
        }

        return $res->only('URL');
    }

    public function generateNotificationHash(array $data): string
    {
        $array = [
            $this->notificationPassword,
            $data['KWOTA'],
            $data['ID_PLATNOSCI'],
            $data['ID_ZAMOWIENIA'],
            $data['STATUS'],
            $data['SECURE'],
            $data['SEKRET'],
        ];

        return hash('sha256', implode(';', $array));
    }
}
