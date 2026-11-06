<?php
/**
 * login.php
 * Halaman masuk pengguna menggunakan email & password
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// Jika sudah login, langsung ke beranda
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors  = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $oldEmail = $email;

    // Validasi input dasar
    if ($email === '')    $errors['email']    = 'Email wajib diisi.';
    if ($password === '') $errors['password'] = 'Password wajib diisi.';

    if (empty($errors)) {
        $pdo  = getDB();
        // Gunakan prepared statement — anti SQL Injection
        $stmt = $pdo->prepare('SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verifikasi password dengan password_verify (bcrypt-safe)
        if (!$user || !password_verify($password, $user['password'])) {
            $errors['general'] = 'Email atau password salah.';
        } else {
            // Simpan data ke session setelah login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['role']    = $user['role'];

            // Regenerate session ID untuk mencegah session fixation attack
            session_regenerate_id(true);

            $_SESSION['flash_success'] = 'Selamat datang kembali, ' . $user['nama'] . '!';
            header('Location: index.php');
            exit;
        }
    }
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk – E-Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#E50914', 'primary-dark': '#b5070f' } } }
        }
    </script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col font-sans antialiased">

<!-- Mini Header -->
<header class="bg-primary shadow-md">
    <div class="max-w-screen-xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="index.php" class="flex items-center gap-2">
            <div class="bg-white text-primary font-extrabold text-xl px-3 py-1 rounded-lg">E</div>
            <span class="text-white font-bold text-xl">E-Store</span>
        </a>
        <a href="index.php" class="text-white/80 hover:text-white text-sm transition">← Kembali ke Toko</a>
    </div>
</header>

<main class="flex-1 flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="text-2xl font-extrabold text-gray-900">Masuk ke Akun Anda</h1>
            <p class="text-gray-500 text-sm mt-1">Belum punya akun?
                <a href="register.php" class="text-primary font-semibold hover:underline">Daftar sekarang</a>
            </p>
        </div>

        <!-- Alert error umum -->
        <?php if (!empty($errors['general'])): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 mb-5 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <?= e($errors['general']) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate
              class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-5">

            <!-- Email -->
            <div>
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= e($oldEmail) ?>"
                       placeholder="nama@email.com"
                       autocomplete="email" required
                       class="form-input <?= isset($errors['email']) ? 'border-red-400' : '' ?>">
                <?php if (isset($errors['email'])): ?>
                <p class="form-error"><?= e($errors['email']) ?></p>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="form-label">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           autocomplete="current-password" required
                           class="form-input pr-10 <?= isset($errors['password']) ? 'border-red-400' : '' ?>">
                    <!-- Toggle show/hide password -->
                    <button type="button" onclick="togglePwd('password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                <p class="form-error"><?= e($errors['password']) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit"
                    class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3 rounded-xl
                           transition hover:scale-[1.02] active:scale-95 shadow-sm">
                Masuk
            </button>
        </form>

        <!-- Akun demo -->
        <div class="mt-5 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-xs text-amber-800">
            <p class="font-bold mb-1">Akun Demo:</p>
            <p>Admin — <span class="font-mono">admin@estore.com</span> / <span class="font-mono">admin123</span></p>
            <p>User &nbsp;— <span class="font-mono">budi@example.com</span> / <span class="font-mono">user123</span></p>
        </div>
    </div>
</main>

<footer class="border-t border-gray-200 text-center py-4 text-gray-400 text-xs">
    &copy; <?= date('Y') ?> E-Store
</footer>

<script>
function togglePwd(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
