<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use App\Models\Company;
use App\Models\User;

$email = 'admin@fechou.com';
$password = 'Fechou@123';
$name = 'Administrador';
$companyName = 'Fechou Demo';

$userModel = new User();
$existingUser = User::findByEmail($email);

if ($existingUser) {
    echo "Default user already exists:\n";
    echo "Email: {$email}\n";
    echo "Password: {$password}\n";
    exit(0);
}

$companyId = (new Company())->create([
    'name' => $companyName,
]);

$userId = $userModel->create([
    'company_id' => $companyId,
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'role' => 'owner',
    'status' => 'active',
    'email_verified_at' => date('Y-m-d H:i:s'),
]);

echo "Default user created successfully:\n";
echo "User ID: {$userId}\n";
echo "Company ID: {$companyId}\n";
echo "Email: {$email}\n";
echo "Password: {$password}\n";