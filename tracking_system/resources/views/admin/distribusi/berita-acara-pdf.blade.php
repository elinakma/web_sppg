<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Berita Acara Distribusi - {{ \Carbon\Carbon::parse($distribusi->tanggal_distribusi)->format('d-m-Y') }}</title>
    <style>
    @page {
        margin-top: 1.48cm;
        margin-bottom: 0.49cm;
        margin-left: 1.75cm;
        margin-right: 1.75cm;
    }
    body { 
        font-family: Arial, sans-serif; 
        font-size: 12px; 
        margin: 0; 
        line-height: 1.5; 
    }

    .header {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin-bottom: 20px;
    }

    .logo {
        width: 60px;
        height: auto;
        flex-shrink: 0;
    }

    .title {
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        line-height: 1.4;
        white-space: nowrap;
    }

    .section { 
        margin-bottom: 30px; 
        padding: 5px; 
    }
    
    table { 
        width: 100%; 
        border-collapse: collapse; 
        margin-top: 10px; 
    }

    td { 
        padding: 6px; 
        vertical-align: top; 
    }
    
    .signature-box { 
        border: 1px solid #000;
        width: 250px; 
        text-align: center;
    }

    .page-break {
        page-break-after: always;
    }
    </style>
</head>
<body>
    @foreach($distribusiSekolah as $ds)
        <?php $sekolah = $ds->sekolah; ?>
        <?php $jumlahPaket = $ds->total_penerima ?? 0; ?>
        <?php $tanggal = \Carbon\Carbon::parse($ds->tanggal_harian)->format('d M Y'); ?>

        <!-- Bagian 1: Penerimaan Paket Makanan -->
        <table width="100%" style="margin-bottom: 20px;">
            <tr>
                <td width="15%" align="left" valign="middle">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/assets/images/logo-sppg.png'))) }}"
                        style="width:80px;">
                </td>
                <td width="85%" align="center" valign="middle">
                    <div style="font-size:16px; font-weight:bold; line-height:1.4;">
                        BERITA ACARA PENERIMAAN PAKET MAKANAN<br>
                        PROGRAM MAKAN BERGIZI GRATIS
                    </div>
                </td>
            </tr>
        </table>

        <div class="section">
            Pada Tanggal <strong>{{ $tanggal }}</strong> jam ________<br>
            Telah diterima paket makanan sejumlah : <strong>{{ $jumlahPaket }} Paket</strong> Makanan Bergizi Gratis dari Satuan Pelayanan Pemenuhan Gizi (SPPG) Tambakromo, Geneng, Kabupaten Ngawi kepada sekolah <strong>{{ $sekolah->nama_sekolah }}</strong>.

            <table width="100%" style="margin-top:10px; margin-bottom:15px;">
                <tr>
                    <!-- KOLOM KIRI -->
                    <td width="50%" valign="top">
                        <strong>Yang menyerahkan :</strong><br><br>
                        Contact Person :<br><br><br>
                        (____________________)
                    </td>

                    <!-- KOLOM KANAN -->
                    <td width="50%" valign="top">
                        <strong>Diterima oleh :</strong> {{ $sekolah->pic }}<br><br>
                        Nomor Telepon : {{ $sekolah->telepon ?? '-' }}<br><br><br>
                        (____________________)
                    </td>
                </tr>
            </table>

            <div style="width: 100%; text-align: right;">
                <div class="signature-box" style="display:inline-block;">
                    <div style="margin-bottom: 50px;">
                        Mengetahui
                    </div>
                    Syahlinas Azigha Zaky, S.Pd.<br>
                    Kepala SPPG Tambakromo, Geneng, Ngawi
                </div>
            </div>
        </div>

        <hr style="margin: 40px 0;">

        <!-- Bagian 2: Pengembalian Alat Makan -->
        <table width="100%" style="margin-bottom: 20px;">
            <tr>
                <td width="15%" align="left" valign="middle">
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('/assets/images/logo-sppg.png'))) }}"
                        style="width:80px;">
                </td>
                <td width="85%" align="center" valign="middle">
                    <div style="font-size:16px; font-weight:bold; line-height:1.4;">
                        BERITA ACARA PENGEMBALIAN ALAT MAKAN<br>
                        PROGRAM MAKAN BERGIZI GRATIS
                    </div>
                </td>
            </tr>
        </table>

        <div class="section">
            Pada Tanggal <strong>{{ $tanggal }}</strong> jam ________<br>
            Telah diserahkan kembali alat makan sejumlah : <strong>{{ $jumlahPaket }} Paket</strong> Makanan Bergizi Gratis kepada Satuan Pelayanan Pemenuhan Gizi (SPPG) Tambakromo, Geneng, Kabupaten Ngawi dari sekolah <strong>{{ $sekolah->nama_sekolah }}</strong>.

            <table width="100%" style="margin-top:10px; margin-bottom:15px;">
                <tr>
                    <!-- KOLOM KIRI -->
                    <td width="50%" valign="top">
                        <strong>Yang menyerahkan :</strong><br><br>
                        Contact Person :<br><br><br>
                        (____________________)
                    </td>

                    <!-- KOLOM KANAN -->
                    <td width="50%" valign="top">
                        <strong>Diterima oleh :</strong> {{ $sekolah->pic }}<br><br>
                        Nomor Telepon : {{ $sekolah->telepon ?? '-' }}<br><br><br>
                        (____________________)
                    </td>
                </tr>
            </table>

            <div style="width: 100%; text-align: right;">
                <div class="signature-box" style="display:inline-block;">
                    <div style="margin-bottom: 50px;">
                        Mengetahui
                    </div>
                    Syahlinas Azigha Zaky, S.Pd.<br>
                    Kepala SPPG Tambakromo, Geneng, Ngawi
                </div>
            </div>
        </div>

        <!-- Page break setelah setiap sekolah + hari -->
        @if(!$loop->last)
            <div class="page-break"></div>
        @endif

    @endforeach

</body>
</html>