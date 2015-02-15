CREATE TABLE "employee" (
  "employee_id" int(11) unsigned NOT NULL AUTO_INCREMENT,
  "created_on" int(10) unsigned DEFAULT NULL,
  "updated_on" int(10) unsigned DEFAULT NULL,
  "hire_date" datetime DEFAULT NULL,
  "emp_status" varchar(255) DEFAULT NULL,
  "group_id" int(11) DEFAULT NULL,
  PRIMARY KEY ("employee_id")
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE "emp_att" (
  "emp_att_id" int(11) unsigned NOT NULL AUTO_INCREMENT,
  "created_on" int(10) unsigned DEFAULT NULL,
  "updated_on" int(10) unsigned DEFAULT NULL,
  "points" varchar(255) DEFAULT NULL,
  "code" varchar(255) DEFAULT NULL,
  "owner_id" varchar(255) DEFAULT NULL,
  "owner_initials" varchar(255) DEFAULT NULL,
  "incident_date" varchar(255) DEFAULT NULL,
  "emp_id" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "corr_act" varchar(255) DEFAULT NULL,
  "vac_hr" varchar(255) DEFAULT NULL,
  "csrv_ticket_id" varchar(255) DEFAULT NULL,
  "approved" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("emp_att_id")
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE "emp_wpi" (
  "emp_wpi_id" int(11) unsigned NOT NULL AUTO_INCREMENT,
  "created_on" int(10) unsigned DEFAULT NULL,
  "updated_on" int(10) unsigned DEFAULT NULL,
  "code" varchar(255) DEFAULT NULL,
  "owner_id" varchar(255) DEFAULT NULL,
  "owner_initials" varchar(255) DEFAULT NULL,
  "incident_date" varchar(255) DEFAULT NULL,
  "emp_id" varchar(255) DEFAULT NULL,
  "description" varchar(255) DEFAULT NULL,
  "corr_act" varchar(255) DEFAULT NULL,
  "csrv_ticket_id" varchar(255) DEFAULT NULL,
  "approved" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("emp_wpi_id")
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE "emp_wtrain" (
  "emp_wtrain_id" int(11) unsigned NOT NULL AUTO_INCREMENT,
  "created_on" int(10) unsigned DEFAULT NULL,
  "updated_on" int(10) unsigned DEFAULT NULL,
  "approved" varchar(255) DEFAULT NULL,
  "code" varchar(255) DEFAULT NULL,
  "owner_id" varchar(255) DEFAULT NULL,
  "owner_initials" varchar(255) DEFAULT NULL,
  "incident_date" varchar(255) DEFAULT NULL,
  "emp_id" varchar(255) DEFAULT NULL,
  "csrv_ticket_id" varchar(255) DEFAULT NULL,
  PRIMARY KEY ("emp_wtrain_id")
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

