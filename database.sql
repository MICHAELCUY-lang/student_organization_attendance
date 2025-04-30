-- Create database
CREATE DATABASE IF NOT EXISTS student_organization_attendance;
USE student_organization_attendance;

-- Create DIVISIONS table
CREATE TABLE DIVISIONS (
    division_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_divisi VARCHAR(100) NOT NULL,
    deskripsi TEXT
);

-- Create MEMBERS table
CREATE TABLE MEMBERS (
    member_id INT PRIMARY KEY AUTO_INCREMENT,
    nim VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    jurusan VARCHAR(100),
    angkatan INT,
    no_hp VARCHAR(20),
    division_id INT NOT NULL,
    FOREIGN KEY (division_id) REFERENCES DIVISIONS(division_id) ON DELETE RESTRICT
);

-- Create MEETINGS table
CREATE TABLE MEETINGS (
    meeting_id INT PRIMARY KEY AUTO_INCREMENT,
    judul_rapat VARCHAR(200) NOT NULL,
    tanggal DATE NOT NULL,
    waktu_mulai TIME NOT NULL,
    waktu_selesai TIME NOT NULL,
    catatan_rapat TEXT,
    CONSTRAINT check_meeting_time CHECK (waktu_mulai < waktu_selesai)
);

-- Create ATTENDANCE table
CREATE TABLE ATTENDANCE (
    attendance_id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL,
    meeting_id INT NOT NULL,
    status_kehadiran ENUM('hadir', 'izin', 'alpa', 'telat') NOT NULL,
    keterangan TEXT,
    waktu_absen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (member_id) REFERENCES MEMBERS(member_id) ON DELETE CASCADE,
    FOREIGN KEY (meeting_id) REFERENCES MEETINGS(meeting_id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (member_id, meeting_id)
);

-- Create PRESENCES_SUMMARY table
CREATE TABLE PRESENCES_SUMMARY (
    summary_id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT NOT NULL UNIQUE,
    total_hadir INT DEFAULT 0,
    total_izin INT DEFAULT 0,
    total_alpa INT DEFAULT 0,
    total_rapat INT DEFAULT 0,
    presentasi_hadir DECIMAL(5,2) DEFAULT 0.00,
    FOREIGN KEY (member_id) REFERENCES MEMBERS(member_id) ON DELETE CASCADE
);

-- Create DOCUMENTS table
CREATE TABLE DOCUMENTS (
    document_id INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    path_file VARCHAR(255) NOT NULL,
    FOREIGN KEY (meeting_id) REFERENCES MEETINGS(meeting_id) ON DELETE CASCADE,
    UNIQUE KEY unique_filename (nama_file)
);

-- Create USERS table
CREATE TABLE USERS (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'operator', 'viewer') NOT NULL
);

-- Create triggers to update PRESENCES_SUMMARY when attendance is added/updated/deleted
DELIMITER //

-- Trigger to update summary when attendance is added
CREATE TRIGGER after_attendance_insert
AFTER INSERT ON ATTENDANCE
FOR EACH ROW
BEGIN
    DECLARE total_meetings INT;
    
    -- Count total meetings
    SELECT COUNT(*) INTO total_meetings FROM MEETINGS;
    
    -- Check if member exists in summary table
    IF (SELECT COUNT(*) FROM PRESENCES_SUMMARY WHERE member_id = NEW.member_id) = 0 THEN
        -- Insert new record
        INSERT INTO PRESENCES_SUMMARY (member_id, total_hadir, total_izin, total_alpa, total_rapat) 
        VALUES (NEW.member_id, 0, 0, 0, total_meetings);
    END IF;
    
    -- Update counts based on status
    IF NEW.status_kehadiran = 'hadir' OR NEW.status_kehadiran = 'telat' THEN
        UPDATE PRESENCES_SUMMARY 
        SET total_hadir = total_hadir + 1, 
            total_rapat = total_meetings
        WHERE member_id = NEW.member_id;
    ELSEIF NEW.status_kehadiran = 'izin' THEN
        UPDATE PRESENCES_SUMMARY 
        SET total_izin = total_izin + 1, 
            total_rapat = total_meetings
        WHERE member_id = NEW.member_id;
    ELSEIF NEW.status_kehadiran = 'alpa' THEN
        UPDATE PRESENCES_SUMMARY 
        SET total_alpa = total_alpa + 1, 
            total_rapat = total_meetings
        WHERE member_id = NEW.member_id;
    END IF;
    
    -- Update attendance percentage
    UPDATE PRESENCES_SUMMARY 
    SET presentasi_hadir = (total_hadir / total_rapat) * 100
    WHERE member_id = NEW.member_id AND total_rapat > 0;
END //

-- Trigger to update summary when attendance is updated
CREATE TRIGGER after_attendance_update
AFTER UPDATE ON ATTENDANCE
FOR EACH ROW
BEGIN
    DECLARE total_meetings INT;
    
    -- Count total meetings
    SELECT COUNT(*) INTO total_meetings FROM MEETINGS;
    
    -- Adjust counts based on old and new status
    IF OLD.status_kehadiran != NEW.status_kehadiran THEN
        -- Reduce count for old status
        IF OLD.status_kehadiran = 'hadir' OR OLD.status_kehadiran = 'telat' THEN
            UPDATE PRESENCES_SUMMARY 
            SET total_hadir = total_hadir - 1
            WHERE member_id = NEW.member_id;
        ELSEIF OLD.status_kehadiran = 'izin' THEN
            UPDATE PRESENCES_SUMMARY 
            SET total_izin = total_izin - 1
            WHERE member_id = NEW.member_id;
        ELSEIF OLD.status_kehadiran = 'alpa' THEN
            UPDATE PRESENCES_SUMMARY 
            SET total_alpa = total_alpa - 1
            WHERE member_id = NEW.member_id;
        END IF;
        
        -- Increase count for new status
        IF NEW.status_kehadiran = 'hadir' OR NEW.status_kehadiran = 'telat' THEN
            UPDATE PRESENCES_SUMMARY 
            SET total_hadir = total_hadir + 1
            WHERE member_id = NEW.member_id;
        ELSEIF NEW.status_kehadiran = 'izin' THEN
            UPDATE PRESENCES_SUMMARY 
            SET total_izin = total_izin + 1
            WHERE member_id = NEW.member_id;
        ELSEIF NEW.status_kehadiran = 'alpa' THEN
            UPDATE PRESENCES_SUMMARY 
            SET total_alpa = total_alpa + 1
            WHERE member_id = NEW.member_id;
        END IF;
    END IF;
    
    -- Update attendance percentage
    UPDATE PRESENCES_SUMMARY 
    SET total_rapat = total_meetings,
        presentasi_hadir = (total_hadir / total_rapat) * 100
    WHERE member_id = NEW.member_id AND total_rapat > 0;
END //

-- Trigger to update summary when attendance is deleted
CREATE TRIGGER after_attendance_delete
AFTER DELETE ON ATTENDANCE
FOR EACH ROW
BEGIN
    DECLARE total_meetings INT;
    
    -- Count total meetings
    SELECT COUNT(*) INTO total_meetings FROM MEETINGS;
    
    -- Reduce count based on deleted status
    IF OLD.status_kehadiran = 'hadir' OR OLD.status_kehadiran = 'telat' THEN
        UPDATE PRESENCES_SUMMARY 
        SET total_hadir = total_hadir - 1
        WHERE member_id = OLD.member_id;
    ELSEIF OLD.status_kehadiran = 'izin' THEN
        UPDATE PRESENCES_SUMMARY 
        SET total_izin = total_izin - 1
        WHERE member_id = OLD.member_id;
    ELSEIF OLD.status_kehadiran = 'alpa' THEN
        UPDATE PRESENCES_SUMMARY 
        SET total_alpa = total_alpa - 1
        WHERE member_id = OLD.member_id;
    END IF;
    
    -- Update attendance percentage
    UPDATE PRESENCES_SUMMARY 
    SET total_rapat = total_meetings,
        presentasi_hadir = (total_hadir / total_rapat) * 100
    WHERE member_id = OLD.member_id AND total_rapat > 0;
END //

-- Trigger to update all summaries when a new meeting is added
CREATE TRIGGER after_meeting_insert
AFTER INSERT ON MEETINGS
FOR EACH ROW
BEGIN
    DECLARE total_meetings INT;
    
    -- Count total meetings
    SELECT COUNT(*) INTO total_meetings FROM MEETINGS;
    
    -- Update all summary records
    UPDATE PRESENCES_SUMMARY 
    SET total_rapat = total_meetings,
        presentasi_hadir = (total_hadir / total_rapat) * 100
    WHERE total_rapat > 0;
END //

DELIMITER ;

-- Insert some sample data
INSERT INTO DIVISIONS (nama_divisi, deskripsi) VALUES
('Academic', 'Focuses on academic activities and development'),
('Events', 'Plans and organizes events and gatherings'),
('Public Relations', 'Handles external communication and branding'),
('Finance', 'Manages organization budget and finances');

-- Sample user for initial access
INSERT INTO USERS (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'); -- password: password

-- Add indexes for performance
CREATE INDEX idx_member_division ON MEMBERS(division_id);
CREATE INDEX idx_meeting_date ON MEETINGS(tanggal);
CREATE INDEX idx_attendance_meeting ON ATTENDANCE(meeting_id);
CREATE INDEX idx_attendance_member ON ATTENDANCE(member_id);
CREATE INDEX idx_document_meeting ON DOCUMENTS(meeting_id);