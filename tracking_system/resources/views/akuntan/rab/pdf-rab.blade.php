<!DOCTYPE html>
<html>
<head>
    <title>RAB - {{ $tanggalAwal }} s/d {{ $tanggalAkhir }}</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            margin: 20px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 25px; 
        }
        th, td { 
            border: 1px solid #333; 
            padding: 8px; 
            text-align: left; 
        }
        th { 
            background-color: #f0f0f0; 
            font-weight: bold;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
        }
        .tgl-merge { 
            background-color: #d4e6ff; 
            font-weight: bold;
            font-size: 13px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <h2>RANCANGAN ANGGARAN BIAYA (RAB) MAKANAN</h2>
    <p>Periode: {{ \Carbon\Carbon::parse($tanggalAwal)->locale('id')->translatedFormat('d M Y') }} s/d 
       {{ \Carbon\Carbon::parse($tanggalAkhir)->locale('id')->translatedFormat('d M Y') }}</p>
</div>

@foreach($dataPdf as $hari)
<table>
    <thead>
        <tr class="tgl-merge">
            <th colspan="3">{{ $hari['tgl_fmt'] }}</th>
        </tr>
        <tr>
            <th width="38%">Nama Bahan</th>
            <th width="15%" class="text-center">Total Order</th>
            <th width="10%" class="text-center">Satuan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($hari['items'] as $item)
        <tr>
            <td>{{ $item['nama_bahan'] }}</td>
            <td class="text-center">{{ is_numeric($item['jumlah']) ? rtrim(rtrim(number_format($item['jumlah'], 2, ',', '.'), '0'), ',') : $item['jumlah'] }}</td>
            <td class="text-center">{{ $item['satuan'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endforeach

</body>
</html>