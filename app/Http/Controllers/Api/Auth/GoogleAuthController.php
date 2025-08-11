<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Akun; // Sesuaikan dengan model Akun Anda
use App\Models\Orang; // Sesuaikan dengan model Orang Anda
use Google_Client; // Gunakan library resmi Google
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoogleAuthController extends Controller
{
    public function handleCallback(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        try {
            $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);
            $payload = $client->verifyIdToken($request->token);

            if (!$payload) {
                throw new \Exception('Invalid Google ID token.');
            }

            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'];
            $avatar = $payload['picture'];

            // [PERBAIKAN] Gunakan transaksi database untuk memastikan integritas data
            $akun = DB::transaction(function () use ($googleId, $email, $name, $avatar) {
                // Cari akun berdasarkan google_id
                $existingAkun = Akun::where('google_id', $googleId)->first();

                if ($existingAkun) {
                    // Jika akun sudah ada, kembalikan akun tersebut
                    return $existingAkun;
                }

                // Jika akun belum ada, buat orang terlebih dahulu
                $orang = Orang::create(['nama_lengkap' => $name]);

                // Kemudian buat akun baru dengan id_orang yang sudah ada
                return Akun::create([
                    'google_id' => $googleId,
                    'id_orang' => $orang->id_orang, // Sertakan id_orang
                    'email' => $email,
                    'id_role' => 2,
                    'status_aktif' => true,
                    'avatar' => $avatar,
                ]);
            });

            $token = $akun->createToken('auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Login berhasil',
                'token' => $token,
                'user' => $akun->load('orang')
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }
}
