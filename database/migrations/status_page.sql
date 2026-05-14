-- Status Page Migration
-- Incidents table for status page

CREATE TABLE IF NOT EXISTS incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('investigating', 'identified', 'monitoring', 'resolved') DEFAULT 'investigating',
    severity ENUM('minor', 'major', 'critical') DEFAULT 'minor',
    affected_components JSON DEFAULT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_started_at (started_at),
    INDEX idx_resolved_at (resolved_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Incident updates table for timeline
CREATE TABLE IF NOT EXISTS incident_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incident_id INT NOT NULL,
    message TEXT NOT NULL,
    status ENUM('investigating', 'identified', 'monitoring', 'resolved') DEFAULT 'investigating',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incident_id) REFERENCES incidents(id) ON DELETE CASCADE,
    INDEX idx_incident_id (incident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
