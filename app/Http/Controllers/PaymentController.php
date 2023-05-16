<?php

namespace App\Http\Controllers;

use App\Enum\PaymentStatusEnum;
use App\Models\Transaction;
use App\Service\PaymentService;
use Illuminate\Http\Request;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;
use YooKassa\Model\NotificationEventType;

class PaymentController extends Controller
{
        //фун вывода оплаты
        public function index(){
            // выводим все транзакиции
            $transactions = Transaction::orderBy('id', 'desc')->get();
            // выводим страницу 
            return view('payment.index', compact('transactions'));
        }
        
        //фун для оплаты, запрос, обращение к сервису который мы уже создали ранее
        public function create(Request $request ,PaymentService $service){
            // формируем платеж
            // создаем платеж
            $amount = (float)$request->input('amount');
            $desc = (string)$request->input('desc');
            // создадим строчки в бд по транзакциям
            $transaction = Transaction::create([
                'amount'=>$amount,
                'desc'=>$desc
            ]);
            // создадим платеж
            if($transaction){
                // создаем сслыку на оплату -> обращаемся к сервису который отвечает за создание, и перечислим что создаем
                $link = $service->createPayment($amount, $desc, [
                    'transaction_id'=>$transaction->id
                ]);
                // выводим пользователя на страницу с оплатой
                return redirect()->away($link);
            }
        }

        //фун для статуса платежки
        public function callback(Request $request, PaymentService $service){
            // получаем значение которое шлет на юкаса
            $source = file_get_contents('php://unit');
            // \Log::error($source);
            // расшифровываем
            $requestBody = json_decode($source, true);
            // подтверждение типа платежа успешного c проверкой на его существования
            $notification = (isset($requestBody['event']) && $requestBody['event'] === NotificationEventType::PAYMENT_SUCCEEDED)
                ? new NotificationSucceeded($requestBody)
                : new NotificationWaitingForCapture($requestBody);
            // получаем объект который он сделал
            $payment = $notification->getObject();
            // для вывода в логи
            // \Log::info(json_decode($payment));
            // добавляем проверку для ожидания
            if(isset($payment->status) && $payment->status === 'waiting_for_capture'){
                // обращаемся к нашему сервесу который создает клиента и обращаемся к встроенной фун-ии
                $service->getClient()->capturePayment([
                    //здесь мы передаем в наш массив платежа сам платеж
                    'amount' => $payment->amount,
                ],
                // также вторым значением передаем id платежа
                $payment->id,
                // делаем его уникальным
                uniqid('', true)
            );
            }
            // проверим наличие статуса 
            if(isset($payment->status) && $payment->status === 'succeeded'){
                // проверим боливое значние платежа 
                if((bool)$payment->paid === true){
                    // выводим нашу метадату которое имеет значение id платежа
                    $metadata = (object)$payment->metadata;
                    // проверим на наличие id платежа
                    if(isset($metadata->transaction_id)){
                        // создаем переменную с id платежа
                        $transactionId = (int)$metadata->transaction_id;
                        // находим её в бд
                        $transaction = Transaction::find($transactionId);
                        // меняем статус
                        $transaction->status = PaymentStatusEnum::CONFIRMED;
                        // сохраним
                        $transaction->save();
                        // делаем проверка на существование кэша который сохранит наши данные о платиже
                        if(cache()->has('amount')){
                            // так мы сохраняем в кэше значние из amount в balance
                            cache()->forever('balance', (float)cache()->get('balance') + (float)$payment->amount->value);
                        }else{
                            // если его нет, то мы добавляем
                            cache()->forever('balance', (float)$payment->amount->value);
                        }//теперь мы можем вывести баланс и статус транзакции
                    }
                }
            }
        }
}
