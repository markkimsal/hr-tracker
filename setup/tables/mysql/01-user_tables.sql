
CREATE TABLE "user_login" (
  "user_login_id" int(10) unsigned NOT NULL AUTO_INCREMENT,
  "username" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  "email" varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "password" varchar(255) CHARACTER SET latin1 NOT NULL,
  "locale" varchar(10) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "tzone" varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "active_on" int(11) NOT NULL DEFAULT '0',
  "active_key" varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "id_provider" varchar(30) CHARACTER SET latin1 NOT NULL DEFAULT 'self',
  "id_provider_token" varchar(200) CHARACTER SET latin1 DEFAULT NULL,
  "reg_date" int(10) unsigned DEFAULT NULL,
  "reg_referrer" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  "reg_cpm" varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  "reg_ip_addr" varchar(39) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "login_date" int(10) unsigned DEFAULT NULL,
  "login_ip_addr" varchar(39) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "login_referrer" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  "agent_key" varchar(60) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "enable_agent" tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY ("user_login_id") USING BTREE,
  UNIQUE KEY "id_username_idx" ("id_provider","username"),
  KEY "email_idx" ("email"),
  KEY "active_on_idx" ("active_on"),
  KEY "active_key_idx" ("active_key"),
  KEY "username_idx" ("username"),
  KEY "agent_key_idx" ("agent_key")
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE "user_group" (
  "user_group_id" int(11) NOT NULL AUTO_INCREMENT,
  "code" varchar(255) CHARACTER SET latin1 NOT NULL,
  "display_name" varchar(255) CHARACTER SET latin1 NOT NULL,
  "active_on" int(11) NOT NULL,
  "active_key" varchar(255) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY ("user_group_id"),
  KEY "code_idx" ("code")
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

CREATE TABLE "user_group_rel" (
  "user_group_rel_id" int(11) unsigned NOT NULL AUTO_INCREMENT,
  "created_on" int(10) unsigned DEFAULT NULL,
  "updated_on" int(10) unsigned DEFAULT NULL,
  "user_group_id" int(10) unsigned DEFAULT NULL,
  "user_login_id" int(10) unsigned DEFAULT NULL,
  PRIMARY KEY ("user_group_rel_id")
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE "user_account" (
  "user_account_id" int(11) unsigned NOT NULL AUTO_INCREMENT,
  "user_login_id" int(11) unsigned NOT NULL,
  "firstname" varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "lastname" varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "contact_email" varchar(200) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "title" varchar(12) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "org_name" varchar(100) CHARACTER SET latin1 DEFAULT NULL,
  "birth_date" int(11) NOT NULL DEFAULT '0',
  "ref_id" varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "ref_no" int(11) NOT NULL DEFAULT '0',
  "created_on" int(10) unsigned DEFAULT NULL,
  "updated_on" int(10) unsigned DEFAULT NULL,
  PRIMARY KEY ("user_account_id"),
  KEY "user_login_idx" ("user_login_id")
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE "user_sess" (
  "user_sess_key" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  "created_on" int(10) unsigned DEFAULT NULL,
  "updated_on" int(10) unsigned DEFAULT NULL,
  "lts" int(11) DEFAULT NULL,
  "ip_addr" varchar(255) DEFAULT NULL,
  "data" blob,
  "saved_on" int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


