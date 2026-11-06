<?php

namespace Tests\Feature;

use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityLoggingTest extends TestCase
{
    use RefreshDatabase;

    private string $email = 'test@example.com';

    private string $password = 'password';

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'email' => $this->email,
            'password' => $this->password,
        ]);

        // Empezar cada prueba con el canal de seguridad limpio.
        foreach (glob(storage_path('logs/security-*.log')) as $file) {
            @unlink($file);
        }
    }

    /**
     * Prueba 1: login válido → INFO + registro success.
     */
    public function test_login_valido_registra_info_y_success(): void
    {
        $response = $this->post('/login', [
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $response->assertRedirect(route('panel'));
        $this->assertAuthenticated();

        $this->assertSame(1, LoginAttempt::where('status', 'success')->count());
        $this->assertSecurityLogContains('INFO: Authentication success');
    }

    /**
     * Prueba 2: dos contraseñas incorrectas → dos WARNING + dos registros failed.
     */
    public function test_dos_passwords_incorrectas_registran_dos_warning(): void
    {
        $this->from('/login')->post('/login', [
            'email' => $this->email,
            'password' => 'clave-mala-1',
        ])->assertSessionHasErrors('email');

        $this->from('/login')->post('/login', [
            'email' => $this->email,
            'password' => 'clave-mala-2',
        ])->assertSessionHasErrors('email');

        $this->assertSame(2, LoginAttempt::where('status', 'failed')->count());
        $this->assertSame(2, substr_count($this->securityLog(), 'WARNING: Authentication failed'));
        $this->assertSecurityLogDoesNotContain('ALERT: Possible brute force');
    }

    /**
     * Prueba 3: tres fallos en cinco minutos → ALERT en el intento siguiente.
     */
    public function test_tres_fallos_generan_alert_en_el_siguiente_intento(): void
    {
        foreach (range(1, 3) as $i) {
            $this->post('/login', [
                'email' => $this->email,
                'password' => "clave-mala-{$i}",
            ]);
        }

        // Con tres fallos registrados, el intento siguiente debe generar ALERT.
        $this->post('/login', [
            'email' => $this->email,
            'password' => 'clave-mala-4',
        ]);

        $this->assertSecurityLogContains('ALERT: Possible brute force');
        $this->assertSecurityLogContains('"failures":3');
    }

    /**
     * Prueba 4: cinco fallos en cinco minutos → bloqueo de 10 minutos en el intento
     * siguiente (CRITICAL) y respuesta HTTP 429.
     */
    public function test_cinco_fallos_bloquean_la_ip_durante_diez_minutos(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post('/login', [
                'email' => $this->email,
                'password' => "clave-mala-{$i}",
            ]);
        }

        // El intento posterior al quinto fallo debe bloquear: CRITICAL + 429.
        $this->post('/login', [
            'email' => $this->email,
            'password' => $this->password,
        ])->assertStatus(429);

        $this->assertSecurityLogContains('CRITICAL: Temporary IP block');
        $this->assertGuest();
    }

    /**
     * Prueba 5: acceso con IP bloqueada → HTTP 429 + evento/registro blocked.
     */
    public function test_ip_bloqueada_responde_429_y_registra_blocked(): void
    {
        foreach (range(1, 5) as $i) {
            $this->post('/login', [
                'email' => $this->email,
                'password' => "clave-mala-{$i}",
            ]);
        }

        // Fuerza el estado de IP ya bloqueada.
        cache()->put('security:block:'.hash('sha256', '127.0.0.1'), true, now()->addMinutes(10));

        $this->post('/login', [
            'email' => $this->email,
            'password' => $this->password,
        ])->assertStatus(429);

        $this->assertSame(1, LoginAttempt::where('status', 'blocked')->count());
        $this->assertSecurityLogContains('WARNING: Blocked login attempt');
    }

    /**
     * Complemento: el panel de monitoreo exige sesión iniciada.
     */
    public function test_panel_requiere_autenticacion(): void
    {
        $this->get('/panel')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())->get('/panel')->assertOk();
    }

    private function securityLog(): string
    {
        $content = '';

        foreach (glob(storage_path('logs/security-*.log')) as $file) {
            $content .= file_get_contents($file);
        }

        return $content;
    }

    private function assertSecurityLogContains(string $needle): void
    {
        $this->assertStringContainsString(
            $needle,
            $this->securityLog(),
            "El canal security no contiene: {$needle}".PHP_EOL.$this->securityLog()
        );
    }

    private function assertSecurityLogDoesNotContain(string $needle): void
    {
        $this->assertStringNotContainsString(
            $needle,
            $this->securityLog(),
            "El canal security no debia contener: {$needle}"
        );
    }
}
