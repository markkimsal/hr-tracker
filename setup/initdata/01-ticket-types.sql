/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `csrv_ticket_status`
--

LOCK TABLES `csrv_ticket_status` WRITE;
/*!40000 ALTER TABLE `csrv_ticket_status` DISABLE KEYS */;
INSERT INTO `csrv_ticket_status` VALUES (1,'new','New',0,1,'N','000000','primary'),(2,'proc','Processing',0,0,'P','000000','default'),(3,'hold','On Hold',0,0,'H','000000','inverse'),(4,'attn','Needs Attention',0,0,'E','000000','warning'),(5,'approv','Approved',1,0,'A','000000','primary'),(6,'rej','Rejected',1,0,'J','000000','error');
/*!40000 ALTER TABLE `csrv_ticket_status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping data for table `csrv_ticket_type`
--

LOCK TABLES `csrv_ticket_type` WRITE;
/*!40000 ALTER TABLE `csrv_ticket_type` DISABLE KEYS */;
INSERT INTO `csrv_ticket_type` VALUES (1,'WPI','Work Performance','W','FF3333','wpi_model','Emp_Wpimodel','primary'),(2,'ATT','Attendance','Att','3300FF','attendance_model','Emp_Attendancemodel','primary'),(3,'WTR','Safety Training','Sft','00CC00','safety_model','Emp_Safetymodel','primary');
/*!40000 ALTER TABLE `csrv_ticket_type` ENABLE KEYS */;
UNLOCK TABLES;


/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-01-12 12:08:51

