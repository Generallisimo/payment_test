<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <title>Payment</title>
</head>
<body>
    <div class="container d-flex" style="justify-content:center; margin-top: 100px;">
        <div style="">
            <h5>Текущий баланс</h5>
            <p>@if(cache()->has('balance')){{cache()->get('balance')}}@else 0 @endif</p>
            <h2>Пополнить баланс</h2>
            <form  action="{{route('payment_create')}}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="amount" class="form-label">Сумма платежа</label>
                    <input type="number" name="amount" class="from-control">
                </div>
                <div class="mb-3">
                    <label for="desc" class="form-label">Описание</label>
                    <input type="text" name="desc" class="from-control" style="height: 200px;">
                </div>
                <button type="submit" class="btn btn-success">Сохранить</button>
            </form>
            <table class="table mt-5">
                <thead>
                    <tr>
                    <th scope="col">#id</th>
                    <th scope="col">Сумма</th>
                    <th scope="col">Описание</th>
                    <th scope="col">Статус</th>
                    <th scope="col">Дата</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <th scope="row">{{$transaction->id}}</th>
                            <td>{{$transaction->amount}}</td>
                            <td>{{$transaction->desc}}</td>
                            <td>{{$transaction->status}}</td>
                            <td>{{$transaction->updated_at->format('d-m-Y H:i')}}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center;">
                                Транзакций нет
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
        </div>
    </div>
</body>
</html>