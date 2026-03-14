-- JanataConnect Database Schema
-- Pokhara Metropolitan City, Nepal

CREATE DATABASE IF NOT EXISTS janataconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE janataconnect;

-- Issues Table
CREATE TABLE IF NOT EXISTS issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(120),
    ward VARCHAR(10) NOT NULL,
    category VARCHAR(60) NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(300),
    photo VARCHAR(300),
    date DATE NOT NULL,
    status ENUM('Pending', 'In Progress', 'Resolved') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Volunteers Table
CREATE TABLE IF NOT EXISTS volunteers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    dob DATE,
    gender VARCHAR(20),
    citizenship_no VARCHAR(60),
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(120),
    address VARCHAR(300),
    ward VARCHAR(10),
    skills TEXT,
    other_skills VARCHAR(300),
    availability_days VARCHAR(100),
    availability_time VARCHAR(100),
    has_experience TINYINT(1) DEFAULT 0,
    prev_organization VARCHAR(200),
    emergency_contact VARCHAR(120),
    emergency_phone VARCHAR(20),
    agreed TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tokens Table
CREATE TABLE IF NOT EXISTS tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(120),
    office VARCHAR(200) NOT NULL,
    service VARCHAR(200) NOT NULL,
    date DATE NOT NULL,
    time_slot VARCHAR(30) NOT NULL,
    token_number VARCHAR(20) NOT NULL UNIQUE,
    status ENUM('Active', 'Completed', 'Cancelled') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Admin Table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(120),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Insert default admin (password: admin123)
INSERT INTO admin_users (username, password, name)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Admin')
ON DUPLICATE KEY UPDATE username = username;

-- Sample Data for Issues
INSERT INTO issues (name, phone, email, ward, category, title, description, location, date, status) VALUES
('Ram Bahadur Thapa', '9856012345', 'ram@email.com', '5', 'Road Problem', 'Large pothole near school', 'There is a dangerous pothole on the main road near Mahendra Pool School that has caused two accidents this week.', 'Mahendra Pool, Ward 5', CURDATE(), 'Pending'),
('Sita Gurung', '9841098765', 'sita@email.com', '8', 'Street Light', 'Street lights not working', 'Three consecutive street lights on Prithvi Chowk are not working since two weeks causing safety issues at night.', 'Prithvi Chowk, Ward 8', DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'In Progress'),
('Hari Shrestha', '9812345678', 'hari@email.com', '12', 'Waste Management', 'Garbage not collected for 5 days', 'The garbage truck has not come to collect waste for more than 5 days. The smell is unbearable and is a health hazard.', 'Newroad, Ward 12', DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Resolved');

-- Sample Volunteers
INSERT INTO volunteers (name, phone, email, ward, skills, availability_days, availability_time, created_at) VALUES
('Anita Sharma', '9800112233', 'anita@email.com', '3', 'First Aid,Teaching,Social Work', 'Weekends', 'Morning', NOW()),
('Bikash Poudel', '9811223344', 'bikash@email.com', '7', 'Technology / IT,Photography,Event Management', 'Anytime', 'Afternoon', NOW());

-- Sample Tokens
INSERT INTO tokens (name, phone, email, office, service, date, time_slot, token_number) VALUES
('Kamala Devi Adhikari', '9845678901', 'kamala@email.com', 'Pokhara Metropolitan Office', 'Citizenship Service', CURDATE(), 'Morning', 'A101'),
('Gopal Karki', '9867890123', 'gopal@email.com', 'Land Revenue Office Pokhara', 'Land Registration', CURDATE(), 'Afternoon', 'B045');


-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    ward VARCHAR(10),
    address VARCHAR(300),
    avatar VARCHAR(10) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    email_verified TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
) ENGINE=InnoDB;

-- Add user_id column to issues (links issues to users)
ALTER TABLE issues ADD COLUMN user_id INT NULL AFTER id;
ALTER TABLE issues ADD CONSTRAINT fk_issue_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Add user_id to volunteers
ALTER TABLE volunteers ADD COLUMN user_id INT NULL AFTER id;
ALTER TABLE volunteers ADD CONSTRAINT fk_volunteer_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Add user_id to tokens
ALTER TABLE tokens ADD COLUMN user_id INT NULL AFTER id;
ALTER TABLE tokens ADD CONSTRAINT fk_token_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Add user_id to campaign_registrations
ALTER TABLE campaign_registrations ADD COLUMN user_id INT NULL AFTER id;
ALTER TABLE campaign_registrations ADD CONSTRAINT fk_campreg_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;



-- Announcements table
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(250) NOT NULL,
    body TEXT NOT NULL,
    tag VARCHAR(40) DEFAULT 'Notice',
    tag_color VARCHAR(20) DEFAULT '#0a4d8c',
    is_ticker TINYINT(1) DEFAULT 1,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Campaigns table
CREATE TABLE IF NOT EXISTS campaigns (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(250) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(300),
    campaign_date DATE,
    campaign_time VARCHAR(60),
    organizer VARCHAR(150),
    max_volunteers INT DEFAULT 0,
    image VARCHAR(300),
    category VARCHAR(80),
    status ENUM('Upcoming','Ongoing','Completed','Cancelled') DEFAULT 'Upcoming',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Campaign registrations table
CREATE TABLE IF NOT EXISTS campaign_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(120),
    ward VARCHAR(10),
    skills VARCHAR(300),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample announcements
INSERT INTO announcements (title, body, tag, tag_color, is_ticker, is_active) VALUES
('Water Supply Disruption – Ward 3 to 5', 'Water supply will be disrupted on Thursday from 9AM to 2PM due to pipeline maintenance work at Seti Dovan. Please store water in advance.', 'Urgent', '#c0392b', 1, 1),
('Tree Plantation Drive – Phewa Lake Area', 'Join us this Sunday at 7AM for a community tree plantation drive at Phewa Lake. Volunteers are needed. Register through the Volunteer Portal.', 'Event', '#1a7a4a', 1, 1),
('Property Tax Payment Deadline Extended', 'The deadline for fiscal year 2080/81 property tax payment has been extended to Poush 30. Avoid penalties by paying on time at Ward offices.', 'Notice', '#0a4d8c', 1, 1),
('New Online Token System Launched', 'Citizens can now book their government office visit tokens online through JanataConnect. No more long queues at office doors!', 'Info', '#e8a000', 1, 1),
('Ward Office 8 Closed Friday', 'Ward Office 8 will remain closed this Friday for scheduled maintenance. Services will resume Saturday morning.', 'Notice', '#0a4d8c', 1, 1);

-- Sample campaigns
INSERT INTO campaigns (title, description, location, campaign_date, campaign_time, organizer, max_volunteers, category, status, is_active) VALUES
('Phewa Lake Tree Plantation Drive', 'Join us for a massive tree plantation drive along the Phewa Lake shoreline. We aim to plant 500 saplings to restore the natural ecosystem and beautify the lakeside area. Gloves and tools will be provided. Bring water and wear comfortable clothes.', 'Phewa Lake Shore, Pokhara', DATE_ADD(CURDATE(), INTERVAL 5 DAY), '7:00 AM – 11:00 AM', 'Environment Section, Pokhara Metro', 100, 'Environment', 'Upcoming', 1),
('Road Cleaning – Ward 3 & 7', 'Community road and drain cleaning campaign for Ward 3 and Ward 7 main roads. Equipment and refreshments will be provided by the municipality. Help us keep Pokhara clean!', 'Ward 3 Community Hall, Pokhara', DATE_ADD(CURDATE(), INTERVAL 10 DAY), '6:30 AM – 9:30 AM', 'Ward Office 3 & 7', 60, 'Sanitation', 'Upcoming', 1),
('Free Health Camp – Ward 10', 'Free health checkup camp including blood pressure, diabetes screening, eye checkup and general consultation by doctors from Gandaki Medical College. All citizens welcome.', 'Ward 10 Community Center', DATE_ADD(CURDATE(), INTERVAL 14 DAY), '9:00 AM – 4:00 PM', 'Health Section, Pokhara Metro', 200, 'Health', 'Upcoming', 1),
('Disaster Relief Training', 'Two-day training program on basic disaster response, first aid, and search-and-rescue techniques. Certificate will be provided. Priority given to ward-level response team members.', 'Metro Training Hall, Pokhara-30', DATE_ADD(CURDATE(), INTERVAL 20 DAY), '9:00 AM – 5:00 PM (2 days)', 'Disaster Management Unit', 50, 'Disaster Relief', 'Upcoming', 1);
