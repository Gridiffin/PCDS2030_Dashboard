-- PCDS2030 Dashboard Essential Schema

-- Drop existing tables if they exist
DROP TABLE IF EXISTS logs;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS GeneratedReports;
DROP TABLE IF EXISTS Reports;
DROP TABLE IF EXISTS Metrics;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS agencies;

-- Create roles table
CREATE TABLE roles (
    RoleID int(10) AUTO_INCREMENT PRIMARY KEY,
    RoleName VARCHAR(255) NOT NULL UNIQUE
);

-- Create agencies table
CREATE TABLE agencies (
    AgencyID int(10) AUTO_INCREMENT PRIMARY KEY,
    AgencyName VARCHAR(255) NOT NULL,
    Sector VARCHAR(255),
    ContactInfo VARCHAR(255),
    Description VARCHAR(255)
);

-- Create users table
CREATE TABLE users (
    UserID int(10) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    RoleID int(10),
    AgencyID int(10),
    FOREIGN KEY (RoleID) REFERENCES roles(RoleID) ON DELETE SET NULL,
    FOREIGN KEY (AgencyID) REFERENCES agencies(AgencyID) ON DELETE SET NULL
);

-- Create logs table first - important for login tracking
CREATE TABLE logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(UserID) ON DELETE SET NULL
);

-- Create Reports table
CREATE TABLE Reports (
    ReportID int(10) AUTO_INCREMENT PRIMARY KEY,
    AgencyID int(10),
    SubmittedByUserID int(10),
    Quarter int(10),
    SubmissionDate date,
    Status VARCHAR(255),
    Metrics JSON,
    FOREIGN KEY (AgencyID) REFERENCES agencies(AgencyID) ON DELETE SET NULL,
    FOREIGN KEY (SubmittedByUserID) REFERENCES users(UserID) ON DELETE SET NULL
);

-- Create GeneratedReports table
CREATE TABLE GeneratedReports (
    GeneratedReportID int(10) AUTO_INCREMENT PRIMARY KEY,
    Quarter int(10),
    GenerationDate date,
    FilePath VARCHAR(255),
    Status VARCHAR(255),
    AgencyID int(10),
    FOREIGN KEY (AgencyID) REFERENCES agencies(AgencyID) ON DELETE SET NULL
);

-- Create Metrics table
CREATE TABLE Metrics (
    MetricID int(10) AUTO_INCREMENT PRIMARY KEY,
    MetricType VARCHAR(255),
    Data JSON,
    Quarter VARCHAR(10),
    Year YEAR,
    AgencyID int(10),
    FOREIGN KEY (AgencyID) REFERENCES agencies(AgencyID) ON DELETE SET NULL
);

-- Insert default roles
INSERT INTO roles (RoleID, RoleName) VALUES 
(1, 'Admin'),
(2, 'User');

-- Insert default agency
INSERT INTO agencies (AgencyID, AgencyName, Sector, ContactInfo, Description) VALUES 
(1, 'Main Agency', 'Government', 'contact@example.com', 'Default Agency Description');

-- Properly insert admin user with password 'admin123' - using bcrypt hash
INSERT INTO users (UserID, username, password, RoleID, AgencyID) VALUES 
(1, 'admin', '$2y$10$qP0/QBo9pG0aT40JK1wTgefCbH.YsUmvHWVcoperoffT.IM5k4Lru', 1, 1);

-- Properly insert regular user with password 'user123' - using bcrypt hash
INSERT INTO users (UserID, username, password, RoleID, AgencyID) VALUES 
(2, 'user', '$2y$10$lunFVqw9WLx0DfBXUVsWL.4n1prQD2n8gBWMPzYvGkEKTVzlQCmPu', 2, 1);
