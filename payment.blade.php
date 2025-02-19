<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Payment</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function updateNomorTujuan() {
            var metode = document.getElementById("metode").value;
            var nomorTujuan = {
                'tunai': '1234567890',
                'transfer': '0987654321',
                'dana': '1122334455',
                'gopay': '5566778899',
                'qris': '6677889900'
            };
            document.getElementById("nomor_tujuan").value = nomorTujuan[metode];
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Payment</h1>
        <form action="{{ route('payment.process', ['id' => $transaksi->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="metode" class="form-label">Metode Pembayaran</label>
                <select name="metode" id="metode" class="form-control" onchange="updateNomorTujuan()" required>
                    <option value="tunai">Tunai</option>
                    <option value="transfer">Transfer</option>
                    <option value="dana">Dana</option>
                    <option value="gopay">Gopay</option>
                    <option value="qris">QRIS</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="nomor_tujuan" class="form-label">Nomor Tujuan</label>
                <input type="text" name="nomor_tujuan" id="nomor_tujuan" class="form-control" readonly>
            </div>
            <div class="mb-3">
                <label for="bukti_pembayaran" class="form-label">Upload Bukti Pembayaran</label>
                <input type="file" name="bukti_pembayaran" id="bukti_pembayaran" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
</body>
</html>
