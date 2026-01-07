<?php
require_once __DIR__ . '/../Models/User.php';

interface UserRepositoryInterface {
    public function create(array $data): User;
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByUsername(string $username): ?User;
    public function update(User $user): bool;
    public function delete(int $id): bool;
    public function findAll(int $limit = 50, int $offset = 0): array;
    public function updateLastLogin(int $userId): bool;
}

