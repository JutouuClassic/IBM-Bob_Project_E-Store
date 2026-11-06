<?php
/**
 * gen_hash.php
 * File sementara untuk generate hash password yang benar.
 * HAPUS file ini setelah selesai digunakan!
 */

$passwords = [
    'admin123' => password_hash('admin123', PASSWORD_DEFAULT),
    'user123'  => password_hash('user123',  PASSWORD_DEFAULT),
];

echo '<pre style="font-family:monospace; font-size:14px; padding:20px;">';
echo "Hash untuk admin123:\n" . $passwords['admin123'] . "\n\n";
echo "Hash untuk user123:\n"  . $passwords['user123']  . "\n\n";
echo "---\n\nQuery UPDATE untuk phpMyAdmin:\n\n";
echo "UPDATE users SET password = '" . $passwords['admin123'] . "' WHERE email = 'admin@estore.com';\n\n";
echo "UPDATE users SET password = '" . $passwords['user123']  . "' WHERE email = 'budi@example.com';\n";
echo '</pre>';
