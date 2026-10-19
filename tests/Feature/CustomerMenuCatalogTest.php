<?php

namespace Tests\Feature;

use App\Models\Produk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerMenuCatalogTest extends TestCase
{
    use RefreshDatabase;

    /** Buat $jumlah produk aktif + 1 produk nonaktif (yang tidak boleh tampil). */
    private function seedKatalog(int $jumlahAktif): void
    {
        for ($i = 1; $i <= $jumlahAktif; $i++) {
            Produk::create([
                'nama_produk' => "Menu Aktif {$i}",
                'kategori' => 'Signature Coffee',
                'harga' => 10000 + $i,
                'status' => 'aktif',
            ]);
        }

        Produk::create([
            'nama_produk' => 'Menu Nonaktif',
            'kategori' => 'Signature Coffee',
            'harga' => 99999,
            'status' => 'nonaktif',
        ]);
    }

    private function jumlahKartu(string $html): int
    {
        return substr_count($html, 'data-menu-item');
    }

    public function test_katalog_menampilkan_semua_produk_aktif_tanpa_pembatas(): void
    {
        $this->seedKatalog(7);

        $html = $this->get('/order/menu')->assertOk()->getContent();

        $this->assertSame(7, $this->jumlahKartu($html), 'Katalog harus menampilkan semua produk aktif.');
        $this->assertStringContainsString('7 Menu Tersedia', $html);
        $this->assertStringNotContainsString('Menu Nonaktif', $html);
    }

    public function test_katalog_tidak_dibatasi_angka_fiks_saat_produk_bertambah(): void
    {
        $this->seedKatalog(4);
        $this->assertStringContainsString('4 Menu Tersedia', $this->get('/order/menu')->getContent());

        // Admin menambah produk lagi -> angka label ikut naik otomatis.
        for ($i = 5; $i <= 12; $i++) {
            Produk::create([
                'nama_produk' => "Menu Aktif {$i}",
                'kategori' => 'Snack & Dessert',
                'harga' => 10000 + $i,
                'status' => 'aktif',
            ]);
        }

        $html = $this->get('/order/menu')->getContent();
        $this->assertSame(12, $this->jumlahKartu($html));
        $this->assertStringContainsString('12 Menu Tersedia', $html);
    }

    public function test_kategori_url_yang_tidak_dikenal_menampilkan_semua_produk(): void
    {
        $this->seedKatalog(6);

        // URL basi / kategori lama yang sudah tidak ada di database.
        foreach (['Manual Brew', 'Non-Coffee', 'kategori-hilang'] as $kategori) {
            $html = $this->get('/order/menu?kategori='.urlencode($kategori))->assertOk()->getContent();

            $this->assertSame(6, $this->jumlahKartu($html), "kategori={$kategori} tidak boleh menyisakan subset produk.");
            $this->assertStringContainsString('6 Menu Tersedia', $html);
        }
    }

    public function test_chip_kategori_diambil_dari_database_bukan_hardcode(): void
    {
        $this->seedKatalog(2);
        Produk::create([
            'nama_produk' => 'Es Teh Segar',
            'kategori' => 'Minuman Dingin',
            'harga' => 7000,
            'status' => 'aktif',
        ]);

        $html = $this->get('/order/menu')->assertOk()->getContent();

        // Kategori baru dari Admin langsung punya chip di Customer.
        $this->assertStringContainsString('data-category="Minuman Dingin"', $html);
        // Chip mati yang tidak punya produk di database tidak muncul lagi.
        $this->assertStringNotContainsString('data-category="Manual Brew"', $html);
        $this->assertStringNotContainsString('data-category="Non-Coffee"', $html);
    }

    public function test_filter_kategori_aktif_menampilkan_hanya_kategori_tersebut(): void
    {
        $this->seedKatalog(3);
        Produk::create([
            'nama_produk' => 'Roti Bakar',
            'kategori' => 'Snack & Dessert',
            'harga' => 12000,
            'status' => 'aktif',
        ]);

        $html = $this->get('/order/menu?kategori='.urlencode('Snack & Dessert'))->assertOk()->getContent();

        $this->assertSame(1, $this->jumlahKartu($html));
        $this->assertStringContainsString('Roti Bakar', $html);
        $this->assertStringContainsString('1 Menu Tersedia', $html);
    }
}
