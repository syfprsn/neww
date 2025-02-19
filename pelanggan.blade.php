@extends('layout.side')
@section('content')
<div class="container-fluid">
    <h3 class="fw-semibold">Tabel Pelanggan</h3> 
    <div class="card mt-4"> 
        <div class="card-body">
            <a href="{{ route('pelanggan.create') }}" class="btn btn-primary mb-4"><i class="ti ti-plus mr-2"></i>Tambah Pelanggan</a>
            <div class="table-responsive">
                <table id="data-table" class="table table-striped table-borderless w-100">
                    <thead>
                      <tr>
                        <th scope="col">No</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Username</th>
                        <th scope="col">Password</th>
                        <th scope="col">Alamat</th>
                        <th scope="col">No.Handphone</th>
                        <th scope="col">Level</th>
                        <th scope="col">Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                        @foreach($user as $key => $item)
                      <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->username }}</td>
                        <td>{{ $item->password }}</td>
                        <td>{{ $item->alamat }}</td>
                        <td>{{ $item->no_hp }}</td>
                        <td><span class="badge bg-primary rounded-3 fw-semibold">{{ $item->level }}</span></td>
                        <td>
                            <a href="{{ route('pelanggan.edit', $item->id) }}" class="text-success fs-6 text-decoration-none"><i class="ti ti-edit"></i></a>
                            <form action="{{ route('pelanggan.destroy', $item->id) }}" method="POST" id="deleteform{{ $item->id }}">
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
                swal("Data tidak jadi dihapus!",{
                    icon:"error",
                });
            }
        });
    }
    $(document).ready(function(){
        $('#data-table').DataTable();
    });
</script>
@endsection