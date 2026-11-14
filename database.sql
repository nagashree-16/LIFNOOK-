-- LIFNOOK database
-- Import this file in phpMyAdmin (http://localhost/phpmyadmin) -> Import

CREATE DATABASE IF NOT EXISTS lifnook CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lifnook;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS moods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  mood VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quotes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mood VARCHAR(20) NOT NULL,
  quote_text VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS memories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(120) NOT NULL,
  content TEXT NOT NULL,
  memory_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS goals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(120) NOT NULL,
  description TEXT,
  category VARCHAR(30) NOT NULL DEFAULT 'Personal',
  target_date DATE NULL,
  progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('active','achieved') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS achievements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(120) NOT NULL,
  description TEXT,
  achieved_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Motivational quotes for each mood
INSERT INTO quotes (mood, quote_text) VALUES
('happy', 'Your joy is contagious. Let it light up the path ahead today.'),
('happy', 'Happy moments are the seeds of great memories. Plant a few more today.'),
('happy', 'Ride this good energy. It is proof that you are heading in the right direction.'),
('excited', 'Excitement is your heart saying yes. Follow it with action.'),
('excited', 'Big dreams start with a spark like this one. Keep it burning.'),
('excited', 'Channel this energy into one small step toward your goal today.'),
('calm', 'Peace is a strength. From stillness, your best ideas will grow.'),
('calm', 'A calm mind sees clearly. Trust where it leads you.'),
('calm', 'Slow and steady still moves you forward. Enjoy this quiet moment.'),
('sad', 'It is okay to feel low. Even rain helps things grow. Better days are coming.'),
('sad', 'You have gotten through every hard day so far. You will get through this one too.'),
('sad', 'Be gentle with yourself today. Healing is also progress.'),
('stressed', 'Breathe. You do not have to solve everything today, just the next small thing.'),
('stressed', 'You are stronger than the pressure you feel. One step at a time.'),
('stressed', 'Pause, breathe, reset. Your worth is not measured by your to-do list.'),
('tired', 'Rest is not quitting. It is how you recharge to keep going.'),
('tired', 'You have done enough for now. Take a break, you deserve it.'),
('tired', 'Even the sun rests every night, and it still rises beautifully.');
