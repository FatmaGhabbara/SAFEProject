<?php
/**
 * ============================================
 * USER ENTITY - Model Class
 * SAFEProject - Pure Entity (MVC Pattern)
 * ============================================
 */

require_once __DIR__ . '/../config.php';

class User {
    // Attributes (properties matching database columns)
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $password;
    private $role; // 'user', 'admin', 'counselor'
    private $date_inscription;
    private $statut; // 'actif', 'inactif', 'suspendu'
    // Counselor-specific attributes (NULL if role != 'counselor')
    private $specialite;
    private $biographie;
    private $disponibilite;
    private $nombre_demandes_actives;
    private $statut_counselor; // 'actif', 'inactif', 'en_pause'
    
    // Database connection
    private $db;
    
    // Constructor
    public function __construct($id = null) {
        $this->db = getDB();
        
        if ($id !== null) {
            $this->load($id);
        }
    }
    
    // Destructor
    public function __destruct() {
        $this->db = null;
    }
    
    // Load user from database by ID
    private function load($id) {
        try {
            $sql = "SELECT * FROM utilisateurs WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch();
            if ($data) {
                $this->hydrate($data);
            }
        } catch (PDOException $e) {
            logAction("Error loading user $id: " . $e->getMessage(), 'error');
        }
    }
    
    // Hydrate object from array
    public function hydrate($data) {
        $this->id = $data['id'] ?? null;
        $this->nom = $data['nom'] ?? null;
        $this->prenom = $data['prenom'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role = $data['role'] ?? 'user';
        $this->date_inscription = $data['date_inscription'] ?? null;
        $this->statut = $data['statut'] ?? 'actif';
        // Counselor-specific attributes
        $this->specialite = $data['specialite'] ?? null;
        $this->biographie = $data['biographie'] ?? null;
        // Convert disponibilite to boolean (can be 0/1 from DB or true/false)
        $disponibiliteValue = $data['disponibilite'] ?? null;
        if ($disponibiliteValue === null) {
            $this->disponibilite = null;
        } else {
            $this->disponibilite = ($disponibiliteValue === true || $disponibiliteValue === 1 || $disponibiliteValue === '1');
        }
        $this->nombre_demandes_actives = $data['nombre_demandes_actives'] ?? 0;
        $this->statut_counselor = $data['statut_counselor'] ?? null;
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getNom() {
        return $this->nom;
    }
    
    public function getPrenom() {
        return $this->prenom;
    }
    
    public function getFullName() {
        return $this->prenom . ' ' . $this->nom;
    }
    
    public function getEmail() {
        return $this->email;
    }
    
    public function getPassword() {
        return $this->password;
    }
    
    public function getRole() {
        return $this->role;
    }
    
    public function getDateInscription() {
        return $this->date_inscription;
    }
    
    public function getStatut() {
        return $this->statut;
    }
    
    // Counselor-specific getters
    public function getSpecialite() {
        return $this->specialite;
    }
    
    public function getBiographie() {
        return $this->biographie;
    }
    
    public function getDisponibilite() {
        return $this->disponibilite;
    }
    
    public function getNombreDemandesActives() {
        return $this->nombre_demandes_actives ?? 0;
    }
    
    public function getStatutCounselor() {
        return $this->statut_counselor;
    }
    
    // Setters
    public function setNom($nom) {
        $this->nom = cleanInput($nom);
    }
    
    public function setPrenom($prenom) {
        $this->prenom = cleanInput($prenom);
    }
    
    public function setEmail($email) {
        $this->email = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
    
    public function setPassword($password) {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }
    
    public function setRole($role) {
        $validRoles = ['user', 'admin', 'counselor'];
        $this->role = in_array($role, $validRoles) ? $role : 'user';
    }
    
    public function setStatut($statut) {
        $validStatuts = ['actif', 'inactif', 'suspendu'];
        $this->statut = in_array($statut, $validStatuts) ? $statut : 'actif';
    }
    
    // Counselor-specific setters
    public function setSpecialite($specialite) {
        $this->specialite = (!empty($specialite) && trim($specialite) !== '') ? cleanInput(trim($specialite)) : null;
    }
    
    public function setBiographie($biographie) {
        if (empty($biographie) || trim($biographie) === '') {
            $this->biographie = null;
        } else {
            $this->biographie = cleanInput($biographie);
        }
    }
    
    public function setDisponibilite($disponibilite) {
        $this->disponibilite = $disponibilite === true || $disponibilite === 1 || $disponibilite === '1';
    }
    
    public function setNombreDemandesActives($nombre) {
        $this->nombre_demandes_actives = max(0, intval($nombre));
    }
    
    public function setStatutCounselor($statut) {
        $validStatuts = ['actif', 'inactif', 'en_pause'];
        $this->statut_counselor = in_array($statut, $validStatuts) ? $statut : null;
    }
    
    // Save to database (INSERT or UPDATE)
    public function save() {
        try {
            if ($this->id === null) {
                return $this->insert();
            } else {
                return $this->update();
            }
        } catch (PDOException $e) {
            logAction("Error saving user: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    // Insert new user
    private function insert() {
        try {
            if ($this->role === 'counselor') {
                $sql = "INSERT INTO utilisateurs (nom, prenom, email, password, role, statut, date_inscription, 
                        specialite, biographie, nombre_demandes_actives, statut_counselor) 
                        VALUES (:nom, :prenom, :email, :password, :role, :statut, NOW(), 
                        :specialite, :biographie, :nombre_demandes_actives, :statut_counselor)";
            } else {
                $sql = "INSERT INTO utilisateurs (nom, prenom, email, password, role, statut, date_inscription) 
                        VALUES (:nom, :prenom, :email, :password, :role, :statut, NOW())";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nom', $this->nom);
            $stmt->bindParam(':prenom', $this->prenom);
            $stmt->bindParam(':email', $this->email);
            $stmt->bindParam(':password', $this->password);
            $stmt->bindParam(':role', $this->role);
            $stmt->bindParam(':statut', $this->statut);
            
            if ($this->role === 'counselor') {
                $nombre = $this->nombre_demandes_actives ?? 0;
                
                // Debug log for counselor registration
                logAction("DEBUG Counselor Insert - Specialite: " . ($this->specialite ?? 'NULL') . 
                         ", Biographie length: " . (strlen($this->biographie ?? '') ?? 0) . 
                         ", StatutCounselor: " . ($this->statut_counselor ?? 'NULL') . 
                         ", Nombre: $nombre", 'info');
                
                // Handle NULL values properly for counselor fields
                if ($this->specialite === null || $this->specialite === '') {
                    $stmt->bindValue(':specialite', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindParam(':specialite', $this->specialite);
                }
                
                if ($this->biographie === null || $this->biographie === '') {
                    $stmt->bindValue(':biographie', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindParam(':biographie', $this->biographie);
                }
                
                $stmt->bindParam(':nombre_demandes_actives', $nombre, PDO::PARAM_INT);
                
                if ($this->statut_counselor === null || $this->statut_counselor === '') {
                    $stmt->bindValue(':statut_counselor', null, PDO::PARAM_NULL);
                } else {
                    $stmt->bindParam(':statut_counselor', $this->statut_counselor);
                }
            }
            
            if ($stmt->execute()) {
                $this->id = $this->db->lastInsertId();
                logAction("New user created (ID: {$this->id}) - Email: {$this->email} - Role: {$this->role}", 'info');
                return true;
            }
            
            // Get detailed error info
            $errorInfo = $stmt->errorInfo();
            logAction("Failed to execute insert for user: {$this->email} - Role: {$this->role} - SQL Error: " . print_r($errorInfo, true), 'error');
            return false;
        } catch (PDOException $e) {
            $errorMsg = "Error inserting user: " . $e->getMessage() . " - Email: {$this->email} - Role: {$this->role}";
            if ($this->role === 'counselor') {
                $errorMsg .= " - Specialite: " . ($this->specialite ?? 'NULL') . " - Biographie length: " . (strlen($this->biographie ?? '') ?? 0);
                $errorMsg .= " - StatutCounselor: " . ($this->statut_counselor ?? 'NULL');
            }
            logAction($errorMsg, 'error');
            return false;
        }
    }
    
    // Update existing user
    private function update() {
        // Récupérer le mot de passe actuel de la base de données pour le comparer
        $currentPasswordHash = null;
        try {
            $checkSql = "SELECT password FROM utilisateurs WHERE id = :id";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $checkStmt->execute();
            $currentData = $checkStmt->fetch();
            if ($currentData) {
                $currentPasswordHash = $currentData['password'];
            }
        } catch (PDOException $e) {
            logAction("Error checking current password: " . $e->getMessage(), 'error');
        }
        
        // Déterminer si le mot de passe a été modifié (si le hash est différent)
        $updatePassword = ($currentPasswordHash !== $this->password);
        
        if ($updatePassword) {
            $sql = "UPDATE utilisateurs 
                    SET nom = :nom, prenom = :prenom, email = :email, 
                        password = :password, role = :role, statut = :statut,
                        specialite = :specialite, biographie = :biographie, 
                        disponibilite = :disponibilite, nombre_demandes_actives = :nombre_demandes_actives,
                        statut_counselor = :statut_counselor
                    WHERE id = :id";
        } else {
            // Ne pas mettre à jour le mot de passe
            $sql = "UPDATE utilisateurs 
                    SET nom = :nom, prenom = :prenom, email = :email, 
                        role = :role, statut = :statut,
                        specialite = :specialite, biographie = :biographie, 
                        disponibilite = :disponibilite, nombre_demandes_actives = :nombre_demandes_actives,
                        statut_counselor = :statut_counselor
                    WHERE id = :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
        $stmt->bindParam(':nom', $this->nom);
        $stmt->bindParam(':prenom', $this->prenom);
        $stmt->bindParam(':email', $this->email);
        if ($updatePassword) {
            $stmt->bindParam(':password', $this->password);
        }
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':statut', $this->statut);
        
        // Handle counselor fields (can be NULL)
        $disponibilite = $this->disponibilite ? 1 : 0;
        $nombre = $this->nombre_demandes_actives ?? 0;
        
        // Handle NULL values properly for counselor fields
        if ($this->specialite === null || $this->specialite === '') {
            $stmt->bindValue(':specialite', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':specialite', $this->specialite);
        }
        
        if ($this->biographie === null || $this->biographie === '') {
            $stmt->bindValue(':biographie', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':biographie', $this->biographie);
        }
        
        $stmt->bindParam(':disponibilite', $disponibilite, PDO::PARAM_INT);
        $stmt->bindParam(':nombre_demandes_actives', $nombre, PDO::PARAM_INT);
        
        if ($this->statut_counselor === null || $this->statut_counselor === '') {
            $stmt->bindValue(':statut_counselor', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindParam(':statut_counselor', $this->statut_counselor);
        }
        
        if ($stmt->execute()) {
            logAction("User {$this->id} updated", 'info');
            return true;
        }
        
        return false;
    }
    
    // Delete user
    public function delete() {
        try {
            if ($this->id === null) {
                return false;
            }
            
            $sql = "DELETE FROM utilisateurs WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                logAction("User {$this->id} deleted", 'warning');
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            logAction("Error deleting user: " . $e->getMessage(), 'error');
            return false;
        }
    }
    
    // Helper methods (simple checks only)
    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }
    
    public function isAdmin() {
        return $this->role === 'admin';
    }
    
    public function isCounselor() {
        return $this->role === 'counselor';
    }
    
    public function isClient() {
        return $this->role === 'user';
    }
    
    public function isActive() {
        return $this->statut === 'actif';
    }
    
    // Convert to array
    public function toArray() {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'prenom' => $this->prenom,
            'email' => $this->email,
            'role' => $this->role,
            'date_inscription' => $this->date_inscription,
            'statut' => $this->statut
        ];
    }
}

?>
