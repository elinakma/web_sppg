{{-- resources/views/gizi/menu/_menu-card.blade.php --}}
<div class="border rounded-3 p-3 mb-3 bg-white shadow-sm">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <strong class="d-block">{{ $menu->nama_menu }}</strong>
        </div>
        <div class="d-flex gap-1">
            <button class="soft-btn btn-next btn-edit"
                    data-bs-toggle="modal"
                    data-bs-target="#editMenuModal{{ $menu->id }}"
                    title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <form action="{{ route('gizi.menu.destroy', $menu) }}" method="POST" class="d-inline">
                @csrf @method('DELETE')
                <button class="soft-btn btn-delete" title="Hapus"
                        onclick="return confirm('Hapus menu {{ addslashes($menu->nama_menu) }} beserta semua bahannya?')">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </div>
    <table class="table table-sm table-bordered mb-0">
        <thead class="table-light">
            <tr>
                <th>Bahan</th>
                <th class="text-center" style="width:70px">Jumlah</th>
                <th class="text-center" style="width:80px">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($menu->bahan as $bahan)
            <tr>
                <td>{{ $bahan->nama_bahan }}</td>
                <td class="text-center">
                    {{ is_numeric($bahan->jumlah) ? rtrim(rtrim(number_format($bahan->jumlah, 2, ',', '.'), '0'), ',') : $bahan->jumlah }}
                </td>
                <td class="text-center">{{ $bahan->satuan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>