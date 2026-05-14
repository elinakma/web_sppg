<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Distribusi SPPG</title>
    <style>
        @page { 
            margin: 1.2cm; 
            size: A4;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            font-size: 9pt; /* Font diperkecil agar lebih rapi */
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 10px;
        }
        .header h2 {
            font-size: 14pt;
            margin: 0;
            letter-spacing: 1px;
        }
        .title {
            font-size: 12pt;
            font-weight: bold;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .period-box {
            font-size: 9pt;
            margin-top: 5px;
        }

        /* Styling Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }
        th, td {
            border: 0.5pt solid #ccc; /* Garis lebih tipis */
            padding: 6px 8px;
        }
        th {
            background-color: #f8fafc;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8pt;
            text-align: center;
        }
        
        /* Ringkasan Box */
        .summary-table td {
            background-color: #fff;
        }
        .label-cell {
            background-color: #f1f5f9;
            font-weight: 600;
            width: 20%;
        }

        /* Section Title */
        h4 {
            font-size: 10pt;
            border-left: 4px solid #1e40af;
            padding-left: 8px;
            margin: 25px 0 10px 0;
            color: #1e40af;
            text-transform: uppercase;
        }
        h5 {
            font-size: 9pt;
            margin: 15px 0 5px 0;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 3px;
        }

        /* Utils */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .success { color: #15803d; font-weight: bold; }
        .danger { color: #b91c1c; font-weight: bold; }

        /* Menu List */
        .menu-container {
            display: flex;
            width: 100%;
            margin-bottom: 10px;
        }
        ul {
            margin: 5px 0;
            padding-left: 20px;
            list-style-type: square;
        }
        li { margin-bottom: 2px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>SATUAN PELAYANAN PEMENUHAN GIZI (SPPG) GENENG</h2>
        <div class="title">Rekapitulasi Distribusi Makan Bergizi Gratis</div>
        <div class="period-box">
            Periode: <strong>{{ $rekapData['tglAwalFmt'] }}</strong> s/d <strong>{{ $rekapData['tglAkhirFmt'] }}</strong>
        </div>
    </div>

    <!-- Ringkasan Statistik -->
    <table class="summary-table">
        <tr>
            <td class="label-cell">Total Penerima</td>
            <td class="text-center font-bold">{{ number_format($rekapData['totalPenerima']) }} Porsi</td>
            <td class="label-cell">Jumlah Sekolah</td>
            <td class="text-center font-bold">{{ $rekapData['jumlahSekolah'] }} Sekolah</td>
        </tr>
        <tr>
            <td class="label-cell">Porsi Kecil</td>
            <td class="text-center">{{ number_format($rekapData['porsiKecil']) }}</td>
            <td class="label-cell">Porsi Besar</td>
            <td class="text-center">{{ number_format($rekapData['porsiBesar']) }}</td>
        </tr>
    </table>

    <!-- RAB Harian -->
    <h4>01. Rancangan Anggaran Biaya (RAB)</h4>
    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Hari / Tanggal</th>
                <th class="text-right">Estimasi Kebutuhan</th>
                <th class="text-right">Pagu Anggaran</th>
                <th class="text-right">Selisih (Efisiensi)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapData['rabHarian'] as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $row['nama_hari'] }}</td>
                <td class="text-right">Rp {{ number_format($row['kebutuhan']) }}</td>
                <td class="text-right">Rp {{ number_format($row['pagu_harian']) }}</td>
                <td class="text-right {{ $row['selisih'] < 0 ? 'danger' : 'success' }}">
                    {{ $row['selisih'] < 0 ? '-' : '+' }} Rp {{ number_format(abs($row['selisih'])) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background:#f1f5f9; font-weight:bold;">
                <td colspan="2" class="text-center">GRAND TOTAL</td>
                <td class="text-right">Rp {{ number_format($rekapData['totalKebutuhan']) }}</td>
                <td class="text-right">Rp {{ number_format($rekapData['totalPaguHarian']) }}</td>
                <td class="text-right {{ $rekapData['totalSelisih'] < 0 ? 'danger' : 'success' }}">
                    Rp {{ number_format($rekapData['totalSelisih']) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Menu -->
    <h4 style="page-break-before: auto;">02. Menu Makanan</h4>
    
    <table style="border: none;">
        @foreach($rekapData['menuHarian'] as $hari)
        <tr style="border: none;">
            <td style="border: none; padding: 0;">
                <h5>{{ $hari['nama_hari'] }}</h5>
                <table style="margin-top: 0; margin-bottom: 20px;">
                    <tr>
                        <th width="50%">Porsi Kecil</th>
                        <th width="50%">Porsi Besar</th>
                    </tr>
                    <tr>
                        <td valign="top">
                            <ul>
                                @foreach($hari['menu_kecil'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td valign="top">
                            <ul>
                                @foreach($hari['menu_besar'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        @endforeach
    </table>

</body>
</html>