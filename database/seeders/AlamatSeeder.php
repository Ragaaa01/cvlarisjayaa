<?php

namespace Database\Seeders;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Provinsi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlamatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan pengecekan foreign key untuk sementara agar proses lebih cepat
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Kosongkan tabel untuk menghindari duplikasi saat seeding ulang
        Provinsi::truncate();
        Kabupaten::truncate();
        Kecamatan::truncate();
        Kelurahan::truncate();

        // Data Alamat untuk Kabupaten Indramayu
        $data = [
            'Jawa Barat' => [
                'Indramayu' => [
                    'Anjatan' => ['Anjatan', 'Anjatan Baru', 'Anjatan Utara', 'Bugis', 'Bugis Tua', 'Cilandak', 'Cilandak Lor', 'Kedungwungu', 'Kopyah', 'Lempuyang', 'Mangunjaya', 'Salamdarma', 'Wanguk'],
                    'Arahan' => ['Arahan Kidul', 'Arahan Lor', 'Cidempet', 'Linggajati', 'Pranggong', 'Sukadadi', 'Sukasari', 'Tawangsari'],
                    'Balongan' => ['Balongan', 'Gelarmendala', 'Majakerta', 'Rawadalem', 'Sudimampir', 'Sudimampir Lor', 'Sukareja', 'Sukaurip', 'Tegalsembadra', 'Tegalurung'],
                    'Bangodua' => ['Bangodua', 'Beduyut', 'Karanggetas', 'Malangsari', 'Mulyasari', 'Rancasari', 'Tegalgirang', 'Wanasari'],
                    'Bongas' => ['Bongas', 'Cipaat', 'Cipedang', 'Kertajaya', 'Kertamulya', 'Margamulya', 'Plawangan', 'Sidamulya'],
                    'Cantigi' => ['Cangkring', 'Cantigi Kulon', 'Cantigi Wetan', 'Cemara', 'Lamarantarung', 'Panyingkiran Kidul', 'Panyingkiran Lor'],
                    'Cikedung' => ['Amis', 'Cikedung', 'Cikedung Lor', 'Jambak', 'Jatisura', 'Loyang', 'Mundakjaya'],
                    'Gabuswetan' => ['Babakanjaya', 'Drunten Kulon', 'Drunten Wetan', 'Gabuskulon', 'Gabuswetan', 'Kedokangabus', 'Kedungdawa', 'Rancahan', 'Rancamulya', 'Sekarmulya'],
                    'Gantar' => ['Baleraja', 'Bantarwaru', 'Gantar', 'Mekarjaya', 'Mekarwaru', 'Sanca', 'Situraja'],
                    'Haurgeulis' => ['Cipancuh', 'Haurgeulis', 'Haurkolot', 'Karangtumaritis', 'Kertanegara', 'Mekarjati', 'Sidadadi', 'Sukajati', 'Sumbermulya', 'Wanakaya'],
                    'Indramayu' => ['Bojongsari', 'Dukuh', 'Karanganyar', 'Karangmalang', 'Karangsong', 'Kepandean', 'Lemahabang', 'Lemahmekar', 'Margadadi', 'Pabeanudik', 'Paoman', 'Pekandangan', 'Pekandangan Jaya', 'Plumbon', 'Singajaya', 'Singaraja', 'Tambak', 'Telukagung'],
                    'Jatibarang' => ['Bulak', 'Bulak Lor', 'Jatibarang', 'Jatibarang Baru', 'Jatisawit', 'Jatisawit Lor', 'Kalimati', 'Kebulen', 'Krasak', 'Lohbener', 'Lohbener Lor', 'Malang Semirang', 'Pawidean', 'Pilangsari', 'Sukalila'],
                    'Juntinyuat' => ['Dadap', 'Juntikebon', 'Juntikedokan', 'Juntinyuat', 'Juntiweden', 'Limbangan', 'Lombang', 'Pondoh', 'Sambimaya', 'Segeran', 'Segeran Kidul', 'Tinumpuk'],
                    'Kandanghaur' => ['Bulak', 'Curug', 'Eretan Kulon', 'Eretan Wetan', 'Ilir', 'Karang Anyar', 'Karangmulya', 'Kertawinangun', 'Pareangirang', 'Pranti', 'Soge', 'Wirapanjunan', 'Wirakanan'],
                    'Karangampel' => ['Benda', 'Dukuh Jeruk', 'Dukuh Tengah', 'Mundu', 'Kaplongan Lor', 'Karangampel', 'Karangampel Kidul', 'Pringgacala', 'Sendang', 'Tanjungpura', 'Tanjungsari'],
                    'Kedokan Bunder' => ['Cangkingan', 'Jayalaksana', 'Jayawinangun', 'Kaplongan', 'Kedokan Agung', 'Kedokan Bunder', 'Kedokan Bunder Wetan'],
                    'Kertasemaya' => ['Jambe', 'Jengkok', 'Kertasemaya', 'Kliwed', 'Laranganjambe', 'Lemahayu', 'Manguntara', 'Sukawera', 'Tegalwirangrong', 'Tenajar', 'Tenajar Kidul', 'Tenajar Lor', 'Tulungagung'],
                    'Krangkeng' => ['Dukuhjati', 'Kalianyar', 'Kapringan', 'Kedungwungu', 'Krangkeng', 'Luwunggesik', 'Purwajaya', 'Singakerta', 'Srengseng', 'Tanjakan', 'Tegalmulya'],
                    'Kroya' => ['Jayamulya', 'Kroya', 'Sukamelang', 'Sukaslamet', 'Sumberjaya', 'Sumbon', 'Tanjungkerta', 'Temiyang', 'Temiyangsari'],
                    'Lelea' => ['Cempeh', 'Langgengsari', 'Lelea', 'Nunuk', 'Pangauban', 'Tamansari', 'Telagasari', 'Tempel', 'Tempelkulon', 'Tugu', 'Tunggulpayung'],
                    'Lohbener' => ['Bojongslawi', 'Kiajaran Kulon', 'Kiajaran Wetan', 'Lanjan', 'Langut', 'Larangan', 'Legok', 'Lohbener', 'Pamayahan', 'Rambatan Kulon', 'Sindangkerta', 'Waru'],
                    'Losarang' => ['Cemara Kulon', 'Jangga', 'Jumbleng', 'Krimun', 'Losarang', 'Muntur', 'Pangkalan', 'Pegagan', 'Puntang', 'Rajaiyang', 'Ranjeng', 'Santing'],
                    'Pasekan' => ['Brondong', 'Karanganyar', 'Pabeanilir', 'Pagirikan', 'Pasekan', 'Totoran'],
                    'Patrol' => ['Arjasari', 'Bugel', 'Limpas', 'Mekarsari', 'Patrol', 'Patrol Baru', 'Patrol Lor', 'Sukahaji'],
                    'Sindang' => ['Babadan', 'Dermayu', 'Kenanga', 'Panyindangan Kulon', 'Panyindangan Wetan', 'Penganjang', 'Rambatan Wetan', 'Sindang', 'Terusan', 'Wanantara'],
                    'Sliyeg' => ['Gadingan', 'Longok', 'Majasari', 'Majasih', 'Mekargading', 'Sleman', 'Sleman Lor', 'Sliyeg', 'Sliyeg Lor', 'Sudikampiran', 'Tambi', 'Tambi Lor', 'Tugu', 'Tugu Kidul'],
                    'Sukagumiwang' => ['Bondan', 'Cadangpinggan', 'Cibeber', 'Gedangan', 'Gunungsari', 'Sukagumiwang', 'Tersana'],
                    'Sukra' => ['Bogor', 'Karanglayung', 'Sukra', 'Sukrawetan', 'Sumuradem', 'Sumuradem Timur', 'Tegaltaman', 'Ujunggebang'],
                    'Terisi' => ['Cibereng', 'Cikawung', 'Jatimulya', 'Jatimunggul', 'Karangasem', 'Kendayakan', 'Manggungan', 'Plosokerep', 'Rajasinga'],
                    'Tukdana' => ['Bodas', 'Cangko', 'Gadel', 'Karangkerta', 'Kerticala', 'Lajer', 'Mekarsari', 'Pagedangan', 'Rancajawat', 'Sukadana', 'Sukamulya', 'Sukaperna', 'Tukdana'],
                    'Widasari' => ['Bangkaloa Ilir', 'Bunder', 'Kalensari', 'Kasmaran', 'Kongsijaya', 'Leuwigede', 'Ujungaris', 'Ujungjaya', 'Ujungpendokjaya', 'Widasari'],
                ]
            ]
        ];

        // Memasukkan data ke database
        foreach ($data as $namaProvinsi => $kabupatens) {
            $provinsi = Provinsi::create(['nama_provinsi' => $namaProvinsi]);
            foreach ($kabupatens as $namaKabupaten => $kecamatans) {
                $kabupaten = $provinsi->kabupatens()->create(['nama_kabupaten' => $namaKabupaten]);
                foreach ($kecamatans as $namaKecamatan => $kelurahans) {
                    $kecamatan = $kabupaten->kecamatans()->create(['nama_kecamatan' => $namaKecamatan]);
                    $dataKelurahan = [];
                    foreach ($kelurahans as $namaKelurahan) {
                        $dataKelurahan[] = ['nama_kelurahan' => $namaKelurahan];
                    }
                    $kecamatan->kelurahans()->createMany($dataKelurahan);
                }
            }
        }

        // Aktifkan kembali pengecekan foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
