CREATE TABLE user
(
  id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  login VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  chat_id INT NOT NULL
);
CREATE UNIQUE INDEX user_login_uindex ON user (login);
CREATE UNIQUE INDEX user_chat_id_uindex ON user (chat_id);

CREATE TABLE training
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    type INT DEFAULT 0 NOT NULL,
    status INT DEFAULT 0 NOT NULL,
    CONSTRAINT training_user_id_fk FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE question
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    training_id INT NOT NULL,
    text TEXT NOT NULL,
    correct_answer_id INT NOT NULL,
    status INT DEFAULT 0 NOT NULL,
    CONSTRAINT question_training_id_fk FOREIGN KEY (training_id) REFERENCES training (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE answer
(
    id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    question_id INT NOT NULL,
    text TEXT NOT NULL,
    CONSTRAINT answer_question_id_fk FOREIGN KEY (question_id) REFERENCES question (id) ON DELETE CASCADE ON UPDATE CASCADE
);