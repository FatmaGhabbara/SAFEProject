<?php

require_once __DIR__ . '/../model/user.php';

class AdminController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function getAllUsers() {
        return $this->userModel->getAllUsers();
    }

    public function approveUser($id) {
        return $this->userModel->approveUser($id);
    }

    public function blockUser($id) {
        return $this->userModel->blockUser($id);
    }

    public function deleteUser($id) {
        return $this->userModel->deleteUser($id);
    }

    public function getStats() {
        $users = $this->getAllUsers();
        $totalUsers = count($users);
        $approvedUsers = 0;
        $blockedUsers = 0;
        $pendingUsers = 0;

        foreach ($users as $userData) {
            $user = new User();
            $user->hydrate($userData);
            
            switch ($user->getStatus()) {
                case 'approved': $approvedUsers++; break;
                case 'blocked': $blockedUsers++; break;
                case 'en attente': $pendingUsers++; break;
            }
        }

        return [
            'users' => $totalUsers,
            'approved' => $approvedUsers,
            'blocked' => $blockedUsers,
            'pending' => $pendingUsers
        ];
    }

    public function displayUsersTable() {
        $users = $this->getAllUsers();
        
        if (!empty($users)) {
            echo "<table>";
            echo "<thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>";
            
            foreach ($users as $userData) {
                $user = new User();
                $user->hydrate($userData);
                
                $statusClass = 'status-' . $user->getStatus();
                $statusText = $user->getStatus();
                
                echo "<tr>
                        <td>{$user->getId()}</td>
                        <td>{$user->getFullname()}</td>
                        <td>{$user->getEmail()}</td>
                        <td><span class='status-badge {$statusClass}'>{$statusText}</span></td>
                        <td>";
                
                if ($user->getStatus() !== 'approved') {
                    echo "<a href='users_list.php?action=approve&id={$user->getId()}' class='action-btn approve-btn'>Approuver</a>";
                }
                if ($user->getStatus() !== 'blocked') {
                    echo "<a href='users_list.php?action=block&id={$user->getId()}' class='action-btn block-btn'>Bloquer</a>";
                }
                echo "<a href='users_list.php?action=delete&id={$user->getId()}' class='action-btn delete-btn' onclick='return confirm(\"Supprimer définitivement cet utilisateur ?\")'>Supprimer</a>";
                
                echo "</td></tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p style='text-align: center; padding: 20px;'>Aucun utilisateur trouvé.</p>";
        }
    }
}
?>