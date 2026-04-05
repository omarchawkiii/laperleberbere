-- ========================================
-- BASE DE DONNÉES - LA PERLE BERBÈRE
-- ========================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS laperleberbere;
USE laperleberbere;

-- ========================================
-- TABLE: disponibilites
-- ========================================
CREATE TABLE IF NOT EXISTS disponibilites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date DATE NOT NULL,
  heure TIME NOT NULL,
  statut ENUM('libre', 'réservé') DEFAULT 'libre',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_slot (date, heure)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TABLE: rendezvous
-- ========================================
CREATE TABLE IF NOT EXISTS rendezvous (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  prenom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  telephone VARCHAR(20) NOT NULL,
  raison TEXT,
  disponibilite_id INT NOT NULL,
  statut ENUM('en attente', 'validé') DEFAULT 'en attente',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (disponibilite_id) REFERENCES disponibilites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- DONNÉES DE TEST
-- ========================================

-- Ajouter quelques créneaux disponibles
INSERT INTO disponibilites (date, heure, statut) VALUES
('2026-04-10', '09:00:00', 'libre'),
('2026-04-10', '10:00:00', 'libre'),
('2026-04-10', '11:00:00', 'libre'),
('2026-04-10', '14:00:00', 'libre'),
('2026-04-10', '15:00:00', 'libre'),
('2026-04-11', '09:00:00', 'libre'),
('2026-04-11', '10:00:00', 'libre'),
('2026-04-11', '11:00:00', 'libre'),
('2026-04-11', '14:00:00', 'libre'),
('2026-04-11', '15:00:00', 'libre'),
('2026-04-12', '09:00:00', 'libre'),
('2026-04-12', '10:00:00', 'libre'),
('2026-04-12', '11:00:00', 'libre'),
('2026-04-12', '14:00:00', 'libre'),
('2026-04-12', '15:00:00', 'libre');
