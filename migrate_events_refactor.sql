-- Migration script for single-day events and edit proposals
USE eventos;

-- 1. Create event_edits table to store modification proposals
CREATE TABLE IF NOT EXISTS event_edits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    
    -- Editable fields
    name VARCHAR(255),
    description TEXT,
    date DATE,
    start_time TIME,
    end_time TIME,
    category_id INT,
    is_public BOOLEAN,
    external_link VARCHAR(255),
    link_title VARCHAR(255),
    image_path VARCHAR(255),
    custom_location VARCHAR(255),
    
    status ENUM('Pendente', 'Aprovado', 'Rejeitado') DEFAULT 'Pendente',
    admin_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    processed_by INT,
    
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Update events table for the new time structure
ALTER TABLE events ADD COLUMN IF NOT EXISTS start_time TIME AFTER date;
ALTER TABLE events ADD COLUMN IF NOT EXISTS end_time TIME AFTER start_time;

-- 3. Populate new columns from existing data
UPDATE events SET 
    start_time = TIME(date),
    end_time = CASE 
        WHEN end_date IS NOT NULL THEN TIME(end_date)
        ELSE TIME(DATE_ADD(date, INTERVAL 1 HOUR))
    END;

-- 4. Convert date column to DATE type (removing time component)
-- We keep it as DATETIME for now but we'll use it as DATE in the logic
-- Or we could change it, but it might break existing strict queries
-- Let's just ensure we only use the DATE part in applications.
