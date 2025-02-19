<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Receipt</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Receipt</h1>
        <p>Transaction ID: {{ $pembayaran->id_transaksi }}</p>
        <p>Date: {{ $pembayaran->waktu_pembayaran }}</p>
        <p>Total: ${{ $pembayaran->total }}</p>
        <p>Payment Method: {{ $pembayaran->metode }}</p>
        <p>Destination Number: {{ $pembayaran->nomor_tujuan }}</p>
         <button onclick="window.print()" class="btn btn-primary">Print</button>
    </div>
</body>
</html>
