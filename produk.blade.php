@extends('layout.side')
@section('content')
<div class="container-fluid">
    <h3 class="fw-semibold">Tabel Produk</h3> 
    <div class="card mt-4"> 
        <div class="card-body">
            <a href="{{ route('produk.create') }}" class="btn btn-primary mb-4"><i class="ti ti-plus mr-2"></i>Tambah Produk</a>
            <div class="table-responsive">
                <table id="data-table" class="table table-striped table-borderless w-100">
                    <thead>
                      <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Harga</th>
                        <th scope="col">Stok</th>
                        <th scope="col">Gambar</th>
                        <th scope="col">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                        @foreach($produk as $key => $item)
                      <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->nama_produk }}</td>
                        <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td>{{ $item->stok }}</td>
                        <td>
                            <img src="{{ asset('storage/produk/' . $item->foto_produk) }}" width="100" onerror="this.onerror=null;this.src='{{ asset('storage/produk/default.jpg') }}';">
                        </td>
                        <td>
                            <a href="{{ route('produk.edit', $item->id) }}" class="text-success fs-6 text-decoration-none"><i class="ti ti-edit"></i></a>
                            <form action="{{ route('produk.destroy', $item->id) }}" method="POST" id="deleteform{{ $item->id }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <a class="text-danger fs-6 text-decoration-none cursor-pointer" onclick="confirmDelete('{{ $item->id }}')"><i class="ti ti-trash"></i></a>
                            </form>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    function confirmDelete(e){
        swal({
            title: "Apakah anda yakin?",
            text: "Data yang dihapus tidak dapat dikembalikan",
            icon: "warning",
            buttons: true,
            dangerMode: true
        })
        .then((willDelete) => {
            if(willDelete){
                $('#deleteform' + e).submit();
            } else{
                swal("Data tidak jadi dihapus!", {
                    icon: "error",
                });
            }
        });
    }
    $(document).ready(function(){
        $('#data-table').DataTable();
    });
</script>
@endsection
