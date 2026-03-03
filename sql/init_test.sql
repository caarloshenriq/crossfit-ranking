CREATE DATABASE IF NOT EXISTS `crossfit_test`;
GRANT ALL PRIVILEGES ON `crossfit_test`.* TO 'app'@'%';
FLUSH PRIVILEGES;

USE `crossfit_test`;

CREATE TABLE IF NOT EXISTS `user` (
    `id`   INT          NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `movement` (
    `id`   INT          NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `personal_record` (
    `id`          INT      NOT NULL AUTO_INCREMENT,
    `user_id`     INT      NOT NULL,
    `movement_id` INT      NOT NULL,
    `value`       FLOAT    NOT NULL,
    `date`        DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `test_pr_fk0` FOREIGN KEY (`user_id`)     REFERENCES `user`(`id`),
    CONSTRAINT `test_pr_fk1` FOREIGN KEY (`movement_id`) REFERENCES `movement`(`id`)
);

INSERT INTO `user` (id, name) VALUES
    (1, 'Alice'),
    (2, 'Bob'),
    (3, 'Charlie'),
    (4, 'Diana');

INSERT INTO `movement` (id, name) VALUES
    (1, 'Deadlift'),
    (2, 'Back Squat'),
    (3, 'Bench Press'),
    (4, 'Clean');

INSERT INTO `personal_record` (user_id, movement_id, value, `date`) VALUES
    (1, 1, 150.0, '2021-01-01 00:00:00'),
    (1, 1, 200.0, '2021-01-02 00:00:00'),
    (1, 1, 120.0, '2021-01-05 00:00:00'),
    (2, 1, 180.0, '2021-01-03 00:00:00'),
    (2, 1, 100.0, '2021-01-06 00:00:00'),
    (3, 1, 160.0, '2021-01-04 00:00:00'),
    (4, 1, 140.0, '2021-01-07 00:00:00'),
    (1, 2, 170.0, '2021-01-01 00:00:00'),
    (1, 2, 130.0, '2021-01-03 00:00:00'),
    (2, 2, 170.0, '2021-01-02 00:00:00'),
    (2, 2, 120.0, '2021-01-04 00:00:00'),
    (3, 2, 150.0, '2021-01-05 00:00:00'),
    (4, 2, 130.0, '2021-01-06 00:00:00'),
    (1, 3, 120.0, '2021-01-01 00:00:00'),
    (2, 3, 100.0, '2021-01-02 00:00:00'),
    (3, 3, 100.0, '2021-01-03 00:00:00'),
    (4, 3,  80.0, '2021-01-04 00:00:00');
