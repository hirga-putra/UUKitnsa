SQL

   CREATE DATABASE IF NOT EXISTS db_uuk_tkj;
   USE db_uuk_tkj;

   CREATE TABLE IF NOT EXISTS exams (
       id INT AUTO_INCREMENT PRIMARY KEY,
       title VARCHAR(150) NOT NULL,
       target_class VARCHAR(50) NOT NULL,
       duration INT NOT NULL,
       exam_date DATETIME NOT NULL,
       status ENUM('Mendatang', 'Berlangsung', 'Selesai') NOT NULL DEFAULT 'Mendatang',
       examiner VARCHAR(100) NOT NULL,
       description TEXT NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
