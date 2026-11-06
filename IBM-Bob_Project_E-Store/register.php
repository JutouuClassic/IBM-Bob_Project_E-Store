<?php
/**
 * register.php
 * Halaman pendaftaran akun baru (role default: 'user')
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// Jika sudah login, langsung ke beranda
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old      = $_POST;
    $nama     = trim($_POST['nama']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    // Validasi input
    if ($nama === '')    $errors['nama']     = 'Nama lengkap wajib diisi.';
    if ($email === '')   $errors['email']    = 'Email wajib diisi.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
                         $errors['email']    = 'Format email tidak valid.';
    if (strlen($password) < 6)
                         $errors['password'] = 'Password minimal 6 karakter.';
    if ($confirm !== $password)
                         $errors['confirm']  = 'Konfirmasi password tidak cocok.';

    if (empty($errors)) {
        $pdo = getDB();

        // Cek apakah email sudah terdaftar
        $stmtCheck = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmtCheck->execute([$email]);
        if ($stmtCheck->fetch()) {
            $errors['email'] = 'Email sudah terdaftar. Gunakan email lain atau masuk.';
        } else {
            // Hash password sebelum disimpan
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmtInsert = $pdo->prepare(
                'INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)'
            );
            $stmtInsert->execute([$nama, $email, $hashedPassword, 'user']);

            $_SESSION['flash_success'] = 'Akun berhasil dibuat! Silakan masuk.';
            header('Location: login.php');
            exit;
        }
    }
}

function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
function ov(string $k): string {
    global $old;
    return htmlspecialchars($old[$k] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
}
function he(string $k): bool { global $errors; return isset($errors[$k]); }
function em(string $k): string { global $errors; return htmlspecialchars($errors[$k] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar – E-Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { primary: '#E50914', 'primary-dark': '#b5070f' } } }
        }
    </script>
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col font-sans antialiased">

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
            <h1 class="text-2xl font-extrabold text-gray-900">Buat Akun Baru</h1>
            <p class="text-gray-500 text-sm mt-1">Sudah punya akun?
                <a href="login.php" class="text-primary font-semibold hover:underline">Masuk di sini</a>
            </p>
        </div>

        <form method="POST" action="register.php" novalidate
              class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-5">

            <!-- Nama -->
            <div>
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" id="nama" name="nama"
                       value="<?= ov('nama') ?>"
                       placeholder="contoh: Budi Santoso"
                       autocomplete="name" required
                       class="form-input <?= he('nama') ? 'border-red-400' : '' ?>">
                <?php if (he('nama')): ?><p class="form-error"><?= em('nama') ?></p><?php endif; ?>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email"
                       value="<?= ov('email') ?>"
                       placeholder="nama@email.com"
                       autocomplete="email" required
                       class="form-input <?= he('email') ? 'border-red-400' : '' ?>">
                <?php if (he('email')): ?><p class="form-error"><?= em('email') ?></p><?php endif; ?>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="form-label">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password"
                           placeholder="Min. 6 karakter"
                           autocomplete="new-password" required
                           class="form-input pr-10 <?= he('password') ? 'border-red-400' : '' ?>">
                    <button type="button" onclick="togglePwd('password')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <?php if (he('password')): ?><p class="form-error"><?= em('password') ?></p><?php endif; ?>
            </div>

            <!-- Konfirmasi Password -->
            <div>
                <label for="confirm" class="form-label">Konfirmasi Password</label>
                <input type="password" id="confirm" name="confirm"
                       placeholder="Ulangi password"
                       autocomplete="new-password" required
                       class="form-input <?= he('confirm') ? 'border-red-400' : '' ?>">
                <?php if (he('confirm')): ?><p class="form-error"><?= em('confirm') ?></p><?php endif; ?>
            </div>

            <button type="submit"
                    class="w-full bg-primary hover:bg-primary-dark text-white font-bold py-3 rounded-xl
                           transition hover:scale-[1.02] active:scale-95 shadow-sm">
                Buat Akun
            </button>
        </form>
    </div>
</main>

<footer class="border-t border-gray-200 text-center py-4 text-gray-400 text-xs">
    &copy; <?= date('Y') ?> E-Store
</footer>
<script>
function togglePwd(id) {
    const inp = document.getElementById(id);
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
