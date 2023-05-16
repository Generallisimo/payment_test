<?php 
// берем из докум
namespace App\Service;

use YooKassa\Client;

// создаем класс для работы
class PaymentService
{
    // фун для создания клиента 
    public function getClient(): Client{
    // берем создание из доков
    $client = new Client();
    // получаем идентификатор и секрет ключ из конфига
    $client->setAuth(config('services.yokassa.shop_id'), config('services.yokassa.secret_key'));
    return $client;
    }

    // создание платежа, принимает в себе платеж, описание, массив данных
    public function createPayment (float $amount, string $desc, array $options = []){
        // получаем клиента
        $client = $this->getClient();
        // создаем массив платежа с помощью фун по документации
        $payment = $client->createPayment(
            [
                // платеж
                'amount' => [
                    // то что получаем по итогу 
                    'value' => $amount,
                    // валюта
                    'currency' => 'RUB'
                ],
                // выбираем статус оплаты сразу или нет
                'capture' => false,
                // добавляем массив отвечающий за редирект обработки страницы
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => route('payment')
                ], 
                // передаем дополнительные данные 
                'metadata' => [
                    // передаем id того что хотим открыть после оплаты, здесь пока просто пополнение баланса
                    'transaction_id' => $options['transaction_id'],
                ],
                // укз описание получаемого сообщения
                'description' => $desc
            ],
                // уникальность заявки вторым параметром
                uniqid('', true)
        );
    // редирект на страницу подтверждения
    return $payment->getConfirmation()->getConfirmationUrl(); 
    }
}