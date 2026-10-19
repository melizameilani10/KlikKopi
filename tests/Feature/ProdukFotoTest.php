<?php

namespace Tests\Feature;

use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProdukFotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public'); // file foto
        Storage::fake('local');  // jejak audit admin
    }

    private function makeUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' Test',
            'username' => $role.'_'.uniqid(),
            'email' => $role.'_'.uniqid().'@test.local',
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    /** File PNG 1x1 asli (tanpa ekstensi GD). */
    private function fotoPng(string $nama = 'menu.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
        $path = tempnam(sys_get_temp_dir(), 'klikkopi-foto');
        file_put_contents($path, $png);

        return new UploadedFile($path, $nama, 'image/png', null, true);
    }

    /** File teks biasa — dipakai untuk memastikan non-gambar ditolak. */
    private function fileTeks(string $nama = 'dokumen.txt'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'klikkopi-tek');
        file_put_contents($path, 'ini bukan gambar');

        return new UploadedFile($path, $nama, 'text/plain', null, true);
    }

    private function dataProduk(array $extra = []): array
    {
        return array_merge([
            'nama_produk' => 'Kopi Susu Foto',
            'kategori' => 'Signature Coffee',
            'harga' => 25000,
            'deskripsi' => 'Espresso & susu aren',
            'status' => 'aktif',
        ], $extra);
    }

    public function test_admin_dapat_upload_foto_lewat_kelola_menu(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->post(route('admin.menu.store'), $this->dataProduk(['foto' => $this->fotoPng()]))
            ->assertRedirect(route('admin.menu.index'));

        $produk = Produk::firstOrFail();
        $this->assertNotNull($produk->foto);
        $this->assertStringStartsWith('produk/', $produk->foto);
        Storage::disk('public')->assertExists($produk->foto);
    }

    public function test_file_bukan_gambar_ditolak(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)
            ->from(route('admin.menu.create'))
            ->post(route('admin.menu.store'), $this->dataProduk(['foto' => $this->fileTeks()]))
            ->assertRedirect(route('admin.menu.create'))
            ->assertSessionHasErrors('foto');

        $this->assertDatabaseCount('produk', 0);
    }

    public function test_update_dengan_foto_baru_menghapus_foto_lama(): void
    {
        $admin = $this->makeUser('admin');
        Storage::disk('public')->put('produk/foto-lama.png', 'isi-lama');
        $produk = Produk::create($this->dataProduk(['foto' => 'produk/foto-lama.png']));

        $this->actingAs($admin)
            ->put(route('admin.menu.update', $produk->id_produk), $this->dataProduk(['foto' => $this->fotoPng('baru.png')]))
            ->assertRedirect(route('admin.menu.index'));

        $produk->refresh();
        Storage::disk('public')->assertMissing('produk/foto-lama.png');
        $this->assertNotSame('produk/foto-lama.png', $produk->foto);
        Storage::disk('public')->assertExists($produk->foto);
    }

    public function test_update_tanpa_foto_mempertahankan_foto_lama(): void
    {
        $admin = $this->makeUser('admin');
        Storage::disk('public')->put('produk/foto-lama.png', 'isi-lama');
        $produk = Produk::create($this->dataProduk(['foto' => 'produk/foto-lama.png']));

        $this->actingAs($admin)
            ->put(route('admin.menu.update', $produk->id_produk), $this->dataProduk(['harga' => 27000]))
            ->assertRedirect(route('admin.menu.index'));

        $produk->refresh();
        $this->assertSame('produk/foto-lama.png', $produk->foto);
        $this->assertSame(27000.0, (float) $produk->harga);
        Storage::disk('public')->assertExists('produk/foto-lama.png');
    }

    public function test_hapus_menu_juga_menghapus_file_fotonya(): void
    {
        $admin = $this->makeUser('admin');
        Storage::disk('public')->put('produk/foto-hapus.png', 'isi');
        $produk = Produk::create($this->dataProduk(['foto' => 'produk/foto-hapus.png']));

        $this->actingAs($admin)
            ->delete(route('admin.menu.destroy', $produk->id_produk))
            ->assertRedirect(route('admin.menu.index'));

        Storage::disk('public')->assertMissing('produk/foto-hapus.png');
        $this->assertDatabaseMissing('produk', ['id_produk' => $produk->id_produk]);
    }

    public function test_api_produk_menyertakan_foto_url(): void
    {
        $denganFoto = Produk::create($this->dataProduk(['foto' => 'produk/katalog.png']));
        Produk::create($this->dataProduk(['nama_produk' => 'Tanpa Foto', 'foto' => null]));

        $this->getJson('/api/produk')
            ->assertOk()
            ->assertJsonFragment(['foto_url' => asset('storage/produk/katalog.png')])
            ->assertJsonFragment(['foto_url' => null]);

        $this->getJson('/api/produk/'.$denganFoto->id_produk)
            ->assertOk()
            ->assertJsonPath('data.foto_url', asset('storage/produk/katalog.png'));
    }

    public function test_api_store_dengan_foto(): void
    {
        $admin = $this->makeUser('admin');
        Sanctum::actingAs($admin);

        $this->post('/api/produk', $this->dataProduk(['foto' => $this->fotoPng('api.png')]))
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.foto_url', asset('storage/'.$this->lastFotoPath()));

        Storage::disk('public')->assertExists($this->lastFotoPath());
    }

    public function test_halaman_customer_menampilkan_foto_menu(): void
    {
        Produk::create($this->dataProduk(['foto' => 'produk/katalog.png']));

        $this->get('/order/menu')
            ->assertOk()
            ->assertSee(asset('storage/produk/katalog.png'));
    }

    public function test_form_kelola_menu_mempunyai_input_foto_bersistem_multipart(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get(route('admin.menu.create'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="foto"', false)
            ->assertSee('accept="image/jpeg,image/png,image/webp"', false);
    }

    public function test_form_edit_menampilkan_foto_aktif_dan_tabel_admin_menampilkan_thumbnail(): void
    {
        $admin = $this->makeUser('admin');
        Storage::disk('public')->put('produk/aktif.png', 'isi');
        $produk = Produk::create($this->dataProduk(['foto' => 'produk/aktif.png']));
        $fotoUrl = asset('storage/produk/aktif.png');

        $this->actingAs($admin)->get(route('admin.menu.edit', $produk->id_produk))
            ->assertOk()
            ->assertSee($fotoUrl);

        $this->actingAs($admin)->get(route('admin.menu.index'))
            ->assertOk()
            ->assertSee($fotoUrl);
    }

    public function test_halaman_customer_tanpa_foto_tidak_menggunakan_gambar_rusak(): void
    {
        Produk::create($this->dataProduk(['foto' => null]));

        $this->get('/order/menu')
            ->assertOk()
            ->assertDontSee('<img src=""', false);
    }

    private function lastFotoPath(): string
    {
        return Produk::firstOrFail()->foto;
    }
}
