-- phpMyAdmin SQL Dump
-- version 5.2.1
-- Generation Time: Mar 16, 2026 at 10:30 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vehicle_assist_db`
--
CREATE DATABASE IF NOT EXISTS `vehicle_assist_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `vehicle_assist_db`;

-- --------------------------------------------------------

--
-- Table structure for table `tbladmin`
--

CREATE TABLE `tbladmin` (
  `ID` int(11) NOT NULL,
  `AdminName` varchar(200) DEFAULT NULL,
  `UserName` varchar(200) DEFAULT NULL,
  `MobileNumber` bigint(20) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL,
  `AdminRegdate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbladmin`
--

INSERT INTO `tbladmin` (`ID`, `AdminName`, `UserName`, `MobileNumber`, `Email`, `Password`, `AdminRegdate`) VALUES
(1, 'Admin', 'admin', 1234567890, 'admin@gmail.com', '12022003', '2026-03-16 16:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `tblbooking`
--

CREATE TABLE `tblbooking` (
  `ID` int(11) NOT NULL,
  `BookingNumber` varchar(120) DEFAULT NULL,
  `Name` varchar(200) DEFAULT NULL,
  `MobileNumber` bigint(20) DEFAULT NULL,
  `VehicleType` varchar(100) DEFAULT NULL,
  `VehicleNumber` varchar(100) DEFAULT NULL,
  `Problem` mediumtext DEFAULT NULL,
  `Location` mediumtext DEFAULT NULL,
  `Photo` varchar(200) DEFAULT NULL,
  `AssignTo` int(11) DEFAULT NULL,
  `Status` varchar(100) DEFAULT NULL,
  `BookingDate` timestamp NULL DEFAULT current_timestamp(),
  `CompletionDate` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `ServiceCost` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO tblbooking 
(BookingNumber, Name, MobileNumber, VehicleType, VehicleNumber, Problem, Location, Photo, AssignTo, Status, ServiceCost) VALUES
('BN001', 'Ravi Kumar', 9876543201, 'Bike', 'TN01AB1234', 'Engine not starting', 'Chennai - T Nagar', 'bike1.jpg', 1, 'Pending', NULL),
('BN002', 'Priya Sharma', 9876543202, 'Car', 'TN02CD5678', 'Flat tyre', 'Chennai - Velachery', 'car1.jpg', 2, 'In Progress', 500.00),
('BN003', 'Arjun Reddy', 9876543203, 'Truck', 'TN03EF9012', 'Brake failure', 'Chennai - Guindy', 'truck1.jpg', 3, 'Pending', NULL),
('BN004', 'Meena Raj', 9876543204, 'Scooter', 'TN04GH3456', 'Battery issue', 'Chennai - Adyar', 'scooter1.jpg', 4, 'Completed', 800.00),
('BN005', 'Vikram Singh', 9876543205, 'Car', 'TN05IJ7890', 'Engine overheating', 'Chennai - Tambaram', 'car2.jpg', 5, 'In Progress', 1200.00),
('BN006', 'Suresh Babu', 9876543206, 'Bike', 'TN06KL1122', 'Chain problem', 'Chennai - Anna Nagar', 'bike2.jpg', 6, 'Pending', NULL),
('BN007', 'Kavya Nair', 9876543207, 'Car', 'TN07MN3344', 'Fuel leakage', 'Chennai - Porur', 'car3.jpg', 7, 'Completed', 1500.00),
('BN008', 'Rahul Das', 9876543208, 'Truck', 'TN08OP5566', 'Tyre burst', 'Chennai - Red Hills', 'truck2.jpg', 8, 'In Progress', 2000.00),
('BN009', 'Anita Joseph', 9876543209, 'Scooter', 'TN09QR7788', 'Self start issue', 'Chennai - Mylapore', 'scooter2.jpg', 9, 'Pending', NULL),
('BN010', 'Kiran Patel', 9876543210, 'Car', 'TN10ST9900', 'AC not working', 'Chennai - OMR', 'car4.jpg', 10, 'Completed', 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbldriver`
--

CREATE TABLE `tbldriver` (
  `ID` int(11) NOT NULL,
  `DriverName` varchar(200) DEFAULT NULL,
  `MobileNumber` bigint(20) DEFAULT NULL,
  `Email` varchar(200) DEFAULT NULL,
  `Password` varchar(200) DEFAULT NULL,
  `VehicleTypes` varchar(250) DEFAULT NULL,
  `DriverPhoto` varchar(200) DEFAULT NULL,
  `Status` varchar(100) DEFAULT 'Active',
  `RegDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO `tbldriver` 
(`DriverName`, `MobileNumber`, `Email`, `Password`, `VehicleTypes`, `DriverPhoto`, `Status`) VALUES
('Ravi Kumar', 9876543210, 'ravi@gmail.com', '12345', 'Car', 'ravi.jpg', 'Active'),
('Suresh Babu', 9876543211, 'suresh@gmail.com', '12345', 'Bike', 'suresh.jpg', 'Active'),
('Arun Prakash', 9876543212, 'arun@gmail.com', '12345', 'Truck', 'arun.jpg', 'Inactive'),
('Karthik R', 9876543213, 'karthik@gmail.com', '12345', 'Car', 'karthik.jpg', 'Active'),
('Vijay Kumar', 9876543214, 'vijay@gmail.com', '12345', 'Bus', 'vijay.jpg', 'Active'),
('Manoj S', 9876543215, 'manoj@gmail.com', '12345', 'Auto', 'manoj.jpg', 'Active'),
('Pradeep K', 9876543216, 'pradeep@gmail.com', '12345', 'Car', 'pradeep.jpg', 'Inactive'),
('Lokesh M', 9876543217, 'lokesh@gmail.com', '12345', 'Bike', 'lokesh.jpg', 'Active'),
('Dinesh R', 9876543218, 'dinesh@gmail.com', '12345', 'Truck', 'dinesh.jpg', 'Active'),
('Santhosh P', 9876543219, 'santhosh@gmail.com', '12345', 'Car', 'santhosh.jpg', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `tblfeedback`
--

CREATE TABLE `tblfeedback` (
  `ID` int(11) NOT NULL,
  `BookingNumber` varchar(120) DEFAULT NULL,
  `Rating` int(11) DEFAULT NULL,
  `Feedback` mediumtext DEFAULT NULL,
  `FeedbackDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT INTO tblfeedback (BookingNumber, Rating, Feedback) VALUES
('BN001', 5, 'Excellent service, very quick response'),
('BN002', 4, 'Good service but a bit delay'),
('BN003', 3, 'Average experience'),
('BN004', 5, 'Very satisfied with the service'),
('BN005', 4, 'Mechanic was professional'),
('BN006', 2, 'Service was slow'),
('BN007', 5, 'Highly recommended service'),
('BN008', 3, 'Okay service'),
('BN009', 4, 'Good support and quick fix'),
('BN010', 5, 'Best breakdown service');
-- --------------------------------------------------------

--
-- Table structure for table `tblserviceupdate`
--

CREATE TABLE `tblserviceupdate` (
  `ID` int(11) NOT NULL,
  `BookingNumber` varchar(120) DEFAULT NULL,
  `Status` varchar(100) DEFAULT NULL,
  `Remark` mediumtext DEFAULT NULL,
  `ConditionPhoto` varchar(200) DEFAULT NULL,
  `UpdateDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblpayment`
--

CREATE TABLE `tblpayment` (
  `ID` int(11) NOT NULL,
  `BookingNumber` varchar(120) DEFAULT NULL,
  `PaymentMode` varchar(50) DEFAULT NULL,
  `TransactionID` varchar(100) DEFAULT NULL,
  `PaymentAmount` decimal(10,2) DEFAULT NULL,
  `PaymentStatus` varchar(50) DEFAULT NULL,
  `PaymentDate` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblnotifications`
--

CREATE TABLE `tblnotifications` (
  `ID` int(11) NOT NULL,
  `BookingNumber` varchar(120) DEFAULT NULL,
  `Message` mediumtext DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbladmin`
--
ALTER TABLE `tbladmin`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblbooking`
--
ALTER TABLE `tblbooking`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `AssignTo` (`AssignTo`);

--
-- Indexes for table `tbldriver`
--
ALTER TABLE `tbldriver`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblfeedback`
--
ALTER TABLE `tblfeedback`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblserviceupdate`
--
ALTER TABLE `tblserviceupdate`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblpayment`
--
ALTER TABLE `tblpayment`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tblnotifications`
--
ALTER TABLE `tblnotifications`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbladmin`
--
ALTER TABLE `tbladmin`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblbooking`
--
ALTER TABLE `tblbooking`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbldriver`
--
ALTER TABLE `tbldriver`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblfeedback`
--
ALTER TABLE `tblfeedback`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblserviceupdate`
--
ALTER TABLE `tblserviceupdate`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblpayment`
--
ALTER TABLE `tblpayment`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblnotifications`
--
ALTER TABLE `tblnotifications`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
