<?php

namespace Tests\Feature;

use App\Models\Meja;
use App\Models\MetodePembayaran;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::create([
            'name'     => ucfirst($role).' Test',
            'username' => $role.'_'.uniqid(),
            'email'    => $role.'_'.uniqid().'@test.local',
            'password' => Hash::make('password'),
            'role'     => $role,
        ]);
    }

    private function makeProduk(int $jumlahStok = 10, string $status = 'aktif'): Produk
    {
        $produk = Produk::create([
            'nama_produk' => 'Kopi Test',
            'kategori'    => 'Signature Coffee',
            'harga'       => 20000,
            'status'      => $status,
        ]);

        Stok::create([
            'id_produk'   => $produk->id_produk,
            'jumlah_stok' => $jumlahStok,
            'min_stok'    => 1,
            'updated_at'  => now(),
        ]);

        return $produk;
    }

    private function makeMeja(string $no = '01'): Meja
    {
        return Meja::create(['no_meja' => $no, 'qr_code' => 'MEJA-'.$no, 'status' => 'tersedia']);
    }

    private function makeMetode(): MetodePembayaran
    {
        return MetodePembayaran::create(['nama_metode' => 'QRIS']);
    }

    public function test_customer_dapat_membuat_pesanan_dan_stok_berkurang(): void
    {
        $customer = $this->makeUser('customer');
        $produk = $this->makeProduk(10);
        $meja = $this->makeMeja();
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/pesanan', [
            'id_meja' => $meja->id_meja,
            'items'   => [['id_produk' => $produk->id_produk, 'jumlah' => 3]],
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.status_pesanan', 'pending')
            ->assertJsonPath('data.total_harga', '60000.00');

        $this->assertDatabaseHas('pesanan', ['kode_pesanan' => $response->json('data.kode_pesanan')]);
        $this->assertDatabaseHas('stok', ['id_produk' => $produk->id_produk, 'jumlah_stok' => 7]);
        $this->assertDatabaseHas('meja', ['id_meja' => $meja->id_meja, 'status' => 'terisi']);
    }

    public function test_stok_tidak_cukup_menghasilkan_422(): void
    {
        $customer = $this->makeUser('customer');
        $produk = $this->makeProduk(2);
        $meja = $this->makeMeja();
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/v1/pesanan', [
            'id_meja' => $meja->id_meja,
            'items'   => [['id_produk' => $produk->id_produk, 'jumlah' => 5]],
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseCount('pesanan', 0);
        $this->assertDatabaseCount('detail_pesanan', 0);
        $this->assertDatabaseHas('stok', ['id_produk' => $produk->id_produk, 'jumlah_stok' => 2]);
    }

    public function test_validasi_gagal_menghasilkan_422(): void
    {
        $customer = $this->makeUser('customer');
        Sanctum::actingAs($customer);

        $this->postJson('/api/v1/pesanan', ['items' => []])
            ->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_akses_role_salah_menghasilkan_403(): void
    {
        $customer = $this->makeUser('customer');
        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/kasir/pesanan/pending')->assertStatus(403);
    }

    public function test_alur_lengkap_pesan_bayar_verifikasi_selesai(): void
    {
        $customer = $this->makeUser('customer');
        $kasir = $this->makeUser('kasir');
        $produk = $this->makeProduk(10);
        $meja = $this->makeMeja();
        $metode = $this->makeMetode();

        Sanctum::actingAs($customer);
        $buat = $this->postJson('/api/v1/pesanan', [
            'id_meja' => $meja->id_meja,
            'items'   => [['id_produk' => $produk->id_produk, 'jumlah' => 2]],
        ])->assertStatus(201);

        $kode = $buat->json('data.kode_pesanan');

        $this->postJson("/api/v1/pesanan/{$kode}/bayar", [
            'id_metode'    => $metode->id_metode,
            'jumlah_bayar' => 50000,
        ])->assertStatus(201)->assertJson(['success' => true]);

        Sanctum::actingAs($kasir);
        $this->getJson('/api/v1/kasir/pesanan/pending')
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->putJson("/api/v1/kasir/pesanan/{$kode}/verifikasi", ['status' => 'selesai'])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('pesanan', ['kode_pesanan' => $kode, 'status_pesanan' => 'selesai']);
        $this->assertDatabaseHas('pembayaran', ['status_pembayaran' => 'berhasil']);
        $this->assertDatabaseCount('transaksi', 1);
        $this->assertDatabaseHas('meja', ['id_meja' => $meja->id_meja, 'status' => 'tersedia']);
    }

    public function test_katalog_produk_publik_dapat_diakses_tanpa_login(): void
    {
        $this->makeProduk(5);

        $this->getJson('/api/v1/produk')
            ->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(1, 'data');
    }
}
