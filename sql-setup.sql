-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 19, 2017 at 09:52 AM
-- Server version: 5.7.17-0ubuntu0.16.04.2
-- PHP Version: 7.0.15-0ubuntu0.16.04.4
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
--
-- Database: `helpdb`
--
CREATE DATABASE IF NOT EXISTS `helpdb` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `helpdb`;
DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `addTutor` (IN `username` VARCHAR(30), IN `class` VARCHAR(30))  NO SQL
BEGIN
	INSERT IGNORE INTO Student (`email`, `SName`, `room_location`, `class_of`) VALUES(username, NULL,NULL, NULL);
    INSERT INTO Tutors (course, student_email) VALUES (class, username);
END$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `canHelpUpdate` (IN `username` VARCHAR(30), IN `canHelp_input` INT(1), IN `course_input` VARCHAR(30))  NO SQL
UPDATE teachesCourse
SET canHelp=canHelp_input
WHERE teacher_email=username and
(SELECT Name FROM Course WHERE id=course_input)=Name$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `checkForStudentsTakenClass` (IN `classname` VARCHAR(30))  NO SQL
    SQL SECURITY INVOKER
Select DISTINCT t1.Name, max(t1.willingness) as willingness, max(t1.Year) as Year, t1.hours AS Availability, t1.room_location, t1.email
From ((Select DISTINCT s.email, s.SName AS Name, t.willingness as willingness, c.Year as Year, null as hours, s.room_location
	  	From Student s, Takes t, Course c
   	  	Where s.email=t.student_email and t.course=c.id and c.Name = classname and NOT EXISTS(SELECT * FROM Tutors k, Course c1 WHERE k.student_email=s.email and k.course=c1.id and c1.Name=classname))
      UNION ALL
      (SELECT DISTINCT t2.email, t2.Name, t2.willingness as willingness, t2.Year as Year, h.hours, t2.office_location
       FROM
       (Select DISTINCT s.email, s.TName AS Name, 10 as willingness, max(c.Year) as Year, s.office_location
	  	From Teacher s, Teaches t, Course c
   	  	Where s.email=t.teacher_email and t.course=c.id and c.Name = classname and t.canHelp = 0
       	GROUP BY s.email) t2
       LEFT JOIN availability_view h ON t2.email=h.email)
      UNION ALL
      (SELECT DISTINCT t3.email, t3.Name, t3.willingness as willingness, t3.Year as Year, h.hours, t3.room_location
       FROM
       (Select DISTINCT s.email, s.SName AS Name, 7 as willingness, max(c.Year) as Year, s.room_location
	  	From Student s, Tutors t, Course c
   	  	Where s.email=t.student_email and t.course=c.id and c.Name = classname
      	GROUP BY s.email) t3 LEFT JOIN availability_view h ON t3.email=h.email)
	UNION ALL
	(Select DISTINCT s.email, s.SName AS Name, max(t.willingness) as willingness, max(c.Year) as Year, null as hours, s.room_location
	  	From Student s, Takes t, Course c
   	  	Where s.email=t.student_email and t.course=c.id and c.Class = classname and NOT EXISTS(SELECT * FROM Tutors k, Course c1 WHERE k.student_email=s.email and k.course=c1.id and c1.Class=classname)
        GROUP BY s.email)
      UNION ALL
      (SELECT DISTINCT t4.email, t4.Name, t4.willingness as willingness, t4.Year as Year, h.hours, t4.office_location
       FROM
       (Select DISTINCT s.email, s.TName AS Name, 10 as willingness, max(c.Year) as Year, s.office_location
	  	From Teacher s, Teaches t, Course c
   	  	Where s.email=t.teacher_email and t.course=c.id and c.Class = classname and t.canHelp = 0
      	GROUP BY s.email) t4 LEFT JOIN availability_view h ON t4.email=h.email)
      UNION ALL
      (SELECT DISTINCT t5.email, t5.Name, t5.willingness as willingness, t5.Year as Year, h.hours, t5.room_location
       FROM
       (Select DISTINCT s.email, s.SName AS Name, 7 as willingness, max(c.Year) as Year, s.room_location
	  	From Student s, Tutors t, Course c
   	  	Where s.email=t.student_email and t.course=c.id and c.Class = classname
      	GROUP BY s.email) t5 LEFT JOIN availability_view h ON t5.email=h.email)) t1
GROUP BY t1.Name, t1.hours, t1.room_location, t1.email
ORDER BY willingness DESC, Year DESC$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `displayTAClass` (IN `username` VARCHAR(40))  NO SQL
SELECT c.Name
FROM Tutors t, Course c
WHERE t.student_email=username and c.id=t.course$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `getTAsByTeacher` (IN `Username` VARCHAR(30))  NO SQL
SELECT id,Class,Quarter,`Year`,Section,student_email FROM Teaches LEFT JOIN Course ON (Course.id = Teaches.course) RIGHT JOIN Tutors ON (Tutors.course = Course.id) WHERE teacher_email = Username$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `importCourse` (IN `Username` VARCHAR(30), IN `Number` VARCHAR(30), IN `Section` INT(11), IN `YearTaken` YEAR(4), IN `Quarter` INT(11), IN `Name` VARCHAR(30), IN `Teacher` VARCHAR(30))  BEGIN
	SET @courseID = CONCAT(Number, Section, YearTaken, Quarter);
    INSERT IGNORE INTO Teacher (`email`, `TName`, `office_location`) VALUES (Teacher, NULL, NULL);
    INSERT IGNORE INTO Student (`email`, `SName`, `room_location`, `class_of`) VALUES(Username, NULL,NULL, NULL);
    INSERT IGNORE INTO Course (`id`, `Class`, `Quarter`, `Year`, `Section`, `Name`) VALUES (@courseID, Number, Quarter, YearTaken, Section, Name);
    INSERT IGNORE INTO Teaches (`course`, `teacher_email`) VALUES(@courseID, Teacher);
    INSERT IGNORE INTO Takes (`student_email`, `course`, `willingness`) VALUES (Username, @courseID, 1);
    INSERT IGNORE INTO Autocomplete (`string`) VALUES (Number);
    INSERT IGNORE INTO Autocomplete (`string`) VALUES (Name);
END$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `importProfCourse` (IN `Username` VARCHAR(30), IN `Number` VARCHAR(30), IN `Section` INT(11), IN `YearTaken` YEAR(4), IN `Quarter` INT(11), IN `Name` VARCHAR(30))  BEGIN
	SET @courseID = CONCAT(Number, Section, YearTaken, Quarter);
    INSERT IGNORE INTO Teacher (`email`, `TName`, `office_location`) VALUES (Username, NULL, NULL);
    INSERT IGNORE INTO Course (`id`, `Class`, `Quarter`, `Year`, `Section`, `Name`) VALUES (@courseID, Number, Quarter, YearTaken, Section, Name);
    INSERT IGNORE INTO Teaches (`course`, `teacher_email`) VALUES(@courseID, Username);
END$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `isProf` (IN `Username` VARCHAR(30))  BEGIN
	SELECT COUNT(*) FROM Teacher WHERE email=Username;
END$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `isTutor` (IN `Username` VARCHAR(30))  BEGIN
	SELECT COUNT(*) FROM Tutors WHERE student_email=Username;
END$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `offerPageList` (IN `username` VARCHAR(30))  READS SQL DATA
SELECT id,Class,Name,`Year`,Quarter,Willingness FROM Takes LEFT JOIN Course ON (Takes.course = Course.id) WHERE student_email = username$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `profferPageList` (IN `Username` VARCHAR(30))  BEGIN
SELECT * FROM Teaches LEFT JOIN Course ON (Teaches.course = Course.id) WHERE teacher_email = Username;
END$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `updateInfo` (IN `username` VARCHAR(30), IN `eff` VARCHAR(30), IN `val` VARCHAR(30))  NO SQL
BEGIN
UPDATE Teacher
SET Teacher.office_location = val
WHERE EXISTS(SELECT * FROM (SELECT * FROM Teacher) as t1 WHERE t1.email=username) and eff = 'office_location' and Teacher.email=username;
UPDATE Teacher
SET Teacher.TName = val
WHERE EXISTS(SELECT * FROM (SELECT * FROM Teacher) as t1 WHERE t1.email=username) and eff = 'TName' and Teacher.email=username;
UPDATE Student
SET Student.SName = val
WHERE EXISTS(SELECT * FROM (SELECT * FROM Student) as s1 WHERE s1.email=username) and eff = 'SName' and Student.email=username;
UPDATE Student
SET Student.room_location = val
WHERE EXISTS(SELECT * FROM (SELECT * FROM Student) as s1 WHERE s1.email=username) and eff = 'room_location' and Student.email=username;
UPDATE Student
SET Student.class_of = val
WHERE EXISTS(SELECT * FROM (SELECT * FROM Student) as s1 WHERE s1.email=username) and eff = 'class_of' and Student.email=username;
END$$
DELIMITER ;
-- --------------------------------------------------------
--
-- Table structure for table `Autocomplete`
--
CREATE TABLE `Autocomplete` (
  `string` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------
--
-- Stand-in structure for view `availability_view`
--
CREATE TABLE `availability_view` (
`email` varchar(30)
,`hours` text
);
-- --------------------------------------------------------
--
-- Table structure for table `Course`
--
CREATE TABLE `Course` (
  `id` varchar(40) NOT NULL,
  `Class` varchar(30) NOT NULL,
  `Quarter` varchar(30) NOT NULL,
  `Year` year(4) NOT NULL,
  `Section` tinyint(4) NOT NULL,
  `Name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------
--
-- Table structure for table `HasHelpHours`
--
CREATE TABLE `HasHelpHours` (
  `id` char(32) NOT NULL,
  `tutor_email` varchar(30) NOT NULL,
  `day` varchar(9) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------
--
-- Table structure for table `Student`
--
CREATE TABLE `Student` (
  `email` varchar(30) NOT NULL,
  `SName` varchar(50) DEFAULT NULL,
  `room_location` varchar(50) DEFAULT NULL,
  `class_of` year(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------
--
-- Table structure for table `Takes`
--
CREATE TABLE `Takes` (
  `student_email` varchar(30) NOT NULL,
  `course` varchar(30) NOT NULL,
  `willingness` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------
--
-- Table structure for table `Teacher`
--
CREATE TABLE `Teacher` (
  `email` varchar(30) NOT NULL,
  `TName` varchar(50) DEFAULT NULL,
  `office_location` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------
--
-- Table structure for table `Teaches`
--
CREATE TABLE `Teaches` (
  `teacher_email` varchar(30) NOT NULL,
  `course` varchar(40) NOT NULL,
  `canHelp` int(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------
--
-- Stand-in structure for view `teachesCourse`
--
CREATE TABLE `teachesCourse` (
`id` varchar(40)
,`Class` varchar(30)
,`Quarter` varchar(30)
,`Year` year(4)
,`Section` tinyint(4)
,`Name` varchar(30)
,`teacher_email` varchar(30)
,`course` varchar(40)
,`canHelp` int(1)
);
-- --------------------------------------------------------
--
-- Table structure for table `Tutors`
--
CREATE TABLE `Tutors` (
  `student_email` varchar(30) NOT NULL,
  `course` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
-- --------------------------------------------------------
--
-- Structure for view `availability_view`
--
DROP TABLE IF EXISTS `availability_view`;
-- in use(#1046 - No database selected)
-- --------------------------------------------------------
--
-- Structure for view `teachesCourse`
--
DROP TABLE IF EXISTS `teachesCourse`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `teachesCourse`  AS  select `c`.`id` AS `id`,`c`.`Class` AS `Class`,`c`.`Quarter` AS `Quarter`,`c`.`Year` AS `Year`,`c`.`Section` AS `Section`,`c`.`Name` AS `Name`,`t`.`teacher_email` AS `teacher_email`,`t`.`course` AS `course`,`t`.`canHelp` AS `canHelp` from (`Course` `c` join `Teaches` `t`) where (`c`.`id` = `t`.`course`) ;
--
-- Indexes for dumped tables
--
--
-- Indexes for table `Autocomplete`
--
ALTER TABLE `Autocomplete`
  ADD UNIQUE KEY `string` (`string`);
--
-- Indexes for table `Course`
--
ALTER TABLE `Course`
  ADD PRIMARY KEY (`id`);
--
-- Indexes for table `HasHelpHours`
--
ALTER TABLE `HasHelpHours`
  ADD PRIMARY KEY (`tutor_email`,`day`,`start_time`),
  ADD KEY `id` (`id`);
--
-- Indexes for table `Student`
--
ALTER TABLE `Student`
  ADD PRIMARY KEY (`email`);
--
-- Indexes for table `Takes`
--
ALTER TABLE `Takes`
  ADD PRIMARY KEY (`student_email`,`course`);
--
-- Indexes for table `Teacher`
--
ALTER TABLE `Teacher`
  ADD PRIMARY KEY (`email`);
--
-- Indexes for table `Teaches`
--
ALTER TABLE `Teaches`
  ADD PRIMARY KEY (`teacher_email`,`course`),
  ADD KEY `course` (`course`),
  ADD KEY `teacher_email` (`teacher_email`);
--
-- Indexes for table `Tutors`
--
ALTER TABLE `Tutors`
  ADD PRIMARY KEY (`student_email`,`course`),
  ADD KEY `student_email` (`student_email`),
  ADD KEY `course` (`course`);
--
-- Constraints for dumped tables
--
--
-- Constraints for table `Teaches`
--
ALTER TABLE `Teaches`
  ADD CONSTRAINT `IsATeacher` FOREIGN KEY (`teacher_email`) REFERENCES `Teacher` (`email`),
  ADD CONSTRAINT `TeachesACourse` FOREIGN KEY (`course`) REFERENCES `Course` (`id`);
--
-- Constraints for table `Tutors`
--
ALTER TABLE `Tutors`
  ADD CONSTRAINT `TutorIsAStudent` FOREIGN KEY (`student_email`) REFERENCES `Student` (`email`),
  ADD CONSTRAINT `TutorsACourse` FOREIGN KEY (`course`) REFERENCES `Course` (`id`);
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
