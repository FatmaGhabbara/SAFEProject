<?php

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../config.php';

class UserController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = config::getConnexion();
    }

    /* =======================
       CREATE
    ======================= */
    public function addUser(User $user): bool
    {
        try {
            $hashedPassword = password_hash($user->getPassword(), PASSWORD_DEFAULT);

            $stmt = $this->pdo->prepare(
                'INSERT INTO users 
                (nom, email, password, role, status, profile_picture, created_at)
                VALUES (:n, :e, :p, :r, :s, :pic, :ca)'
            );

            $result = $stmt->execute([
                'n'   => $user->getNom(),
                'e'   => $user->getEmail(),
                'p'   => $hashedPassword,
                'r'   => $user->getRole(),
                's'   => $user->getStatus(),
                'pic' => $user->getProfilePicture(),
                'ca'  => $user->getCreatedAt()
            ]);

            if ($result) {
                $user->setId($this->pdo->lastInsertId());
            }

            return $result;

        } catch (Exception $e) {
            error_log("UserController::addUser() - " . $e->getMessage());
            return false;
        }
    }

    /* =======================
       READ
    ======================= */
    public function listUsers(): array
    {
        try {
            $stmt = $this->pdo->query('SELECT * FROM users ORDER BY created_at DESC');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $users = [];
            foreach ($rows as $row) {
                $users[] = $this->mapRowToUser($row);
            }

            return $users;

        } catch (Exception $e) {
            error_log("UserController::listUsers() - " . $e->getMessage());
            return [];
        }
    }

    public function listUsersAsArray(): array
    {
        try {
            $stmt = $this->pdo->query(
                'SELECT id, nom, email, role, status, profile_picture, created_at 
                 FROM users ORDER BY created_at DESC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            error_log("UserController::listUsersAsArray() - " . $e->getMessage());
            return [];
        }
    }

    public function getUser(int $id): ?User
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return $row ? $this->mapRowToUser($row) : null;

        } catch (Exception $e) {
            error_log("UserController::getUser() - " . $e->getMessage());
            return null;
        }
    }

    public function getUserById(int $id): ?User
    {
        return $this->getUser($id);
    }

    public function getUserByEmail(string $email): ?array
    {
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
            $stmt->execute(['email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

        } catch (Exception $e) {
            error_log("UserController::getUserByEmail() - " . $e->getMessage());
            return null;
        }
    }

    /* =======================
       UPDATE
    ======================= */
    public function updateUser(int $id, User $user): bool
    {
        try {
            $sql = '
                UPDATE users SET
                    nom = :n,
                    email = :e,
                    role = :r,
                    status = :s,
                    profile_picture = :pic,
                    date_naissance = :dn,
                    telephone = :tel,
                    adresse = :addr,
                    bio = :bio,
                    specialite = :spec,
                    updated_at = NOW()
                WHERE id = :id
            ';

            $params = [
                'id'   => $id,
                'n'    => $user->getNom(),
                'e'    => $user->getEmail(),
                'r'    => $user->getRole(),
                's'    => $user->getStatus(),
                'pic'  => $user->getProfilePicture(),
                'dn'   => $user->getDateNaissance(),
                'tel'  => $user->getTelephone(),
                'addr' => $user->getAdresse(),
                'bio'  => $user->getBio(),
                'spec' => $user->getSpecialite()
            ];

            if (!empty($user->getPassword())) {
                $sql = str_replace(
                    'email = :e,',
                    'email = :e, password = :p,',
                    $sql
                );
                $params['p'] = password_hash($user->getPassword(), PASSWORD_DEFAULT);
            }

            return $this->pdo->prepare($sql)->execute($params);

        } catch (Exception $e) {
            error_log("UserController::updateUser() - " . $e->getMessage());
            return false;
        }
    }

    public function updateUserPassword(int $userId, string $newPassword): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE users SET password = :p, updated_at = NOW() WHERE id = :id'
            );

            return $stmt->execute([
                'p'  => password_hash($newPassword, PASSWORD_DEFAULT),
                'id' => $userId
            ]);

        } catch (Exception $e) {
            error_log("UserController::updateUserPassword() - " . $e->getMessage());
            return false;
        }
    }

    public function updateUserStatus(int $id, string $status): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                'UPDATE users SET status = :s, updated_at = NOW() WHERE id = :id'
            );
            return $stmt->execute(['s' => $status, 'id' => $id]);

        } catch (Exception $e) {
            error_log("UserController::updateUserStatus() - " . $e->getMessage());
            return false;
        }
    }

    public function approveUser(int $id): bool
    {
        return $this->updateUserStatus($id, 'actif');
    }

    public function blockUser(int $id): bool
    {
        return $this->updateUserStatus($id, 'suspendu');
    }

    /* =======================
       DELETE
    ======================= */
    public function deleteUser(int $id): bool
    {
        try {
            return $this->pdo
                ->prepare('DELETE FROM users WHERE id = :id')
                ->execute(['id' => $id]);

        } catch (Exception $e) {
            error_log("UserController::deleteUser() - " . $e->getMessage());
            return false;
        }
    }

    /* =======================
       UTILS
    ======================= */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function countUsers(): int
    {
        try {
            return (int) $this->pdo
                ->query('SELECT COUNT(*) FROM users')
                ->fetchColumn();

        } catch (Exception $e) {
            error_log("UserController::countUsers() - " . $e->getMessage());
            return 0;
        }
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    private function mapRowToUser(array $row): User
    {
        $user = new User();
        $user->setId($row['id'])
            ->setNom($row['nom'])
            ->setEmail($row['email'])
            ->setPassword('')
            ->setRole($row['role'])
            ->setStatus($row['status'])
            ->setProfilePicture($row['profile_picture'] ?? '')
            ->setDateNaissance($row['date_naissance'] ?? null)
            ->setTelephone($row['telephone'] ?? null)
            ->setAdresse($row['adresse'] ?? null)
            ->setBio($row['bio'] ?? null)
            ->setSpecialite($row['specialite'] ?? null)
            ->setCreatedAt($row['created_at'])
            ->setUpdatedAt($row['updated_at'] ?? null);

        return $user;
    }
}