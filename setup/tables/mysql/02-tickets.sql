CREATE TABLE "csrv_ticket" (
  "csrv_ticket_id" int(11) NOT NULL AUTO_INCREMENT,
  "csrv_ticket_type_id" tinyint(2) unsigned NOT NULL,
  "csrv_ticket_status_id" tinyint(2) unsigned NOT NULL DEFAULT '0',
  "is_closed" tinyint(2) unsigned NOT NULL DEFAULT '0',
  "is_locked" tinyint(2) unsigned NOT NULL DEFAULT '0',
  "edited_on" int(11) unsigned NOT NULL DEFAULT '0',
  "created_on" int(11) unsigned NOT NULL DEFAULT '0',
  "owner_id" int(11) unsigned NOT NULL DEFAULT '0',
  "cgn_account_id" int(11) unsigned NOT NULL DEFAULT '0',
  "ref_id" int(11) unsigned NOT NULL DEFAULT '0',
  "ref_num" varchar(45) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "description" text CHARACTER SET latin1,
  "updated_on" int(10) unsigned DEFAULT NULL,
  PRIMARY KEY ("csrv_ticket_id"),
  KEY "edited_on_idx" ("edited_on"),
  KEY "created_on_idx" ("created_on"),
  KEY "owner_idx" ("owner_id"),
  KEY "is_closed_idx" ("is_closed"),
  KEY "csrv_ticket_type_id" ("csrv_ticket_type_id"),
  KEY "csrv_ticket_status_id" ("csrv_ticket_status_id")
) ENGINE=InnoDB AUTO_INCREMENT=2356 DEFAULT CHARSET=utf8;

CREATE TABLE "csrv_ticket_type" (
  "csrv_ticket_type_id" int(11) NOT NULL AUTO_INCREMENT,
  "code" char(6) CHARACTER SET latin1 NOT NULL DEFAULT 'ticket',
  "display_name" varchar(25) CHARACTER SET latin1 NOT NULL DEFAULT 'Ticket',
  "abbrv" char(4) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "hex_color" char(8) CHARACTER SET latin1 NOT NULL DEFAULT '0',
  "mod_library" varchar(100) CHARACTER SET latin1 NOT NULL,
  "class_name" varchar(100) CHARACTER SET latin1 NOT NULL,
  "style_name" char(10) NOT NULL DEFAULT 'primary',
  PRIMARY KEY ("csrv_ticket_type_id"),
  KEY "code_idx" ("code")
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE "csrv_ticket_status" (
  "csrv_ticket_status_id" int(11) NOT NULL AUTO_INCREMENT,
  "code" char(6) CHARACTER SET latin1 NOT NULL DEFAULT 'ticket',
  "display_name" varchar(25) CHARACTER SET latin1 NOT NULL DEFAULT 'Ticket',
  "is_terminal" tinyint(2) unsigned NOT NULL DEFAULT '0',
  "is_initial" tinyint(2) unsigned NOT NULL DEFAULT '0',
  "abbrv" char(4) CHARACTER SET latin1 NOT NULL DEFAULT '',
  "hex_color" char(8) CHARACTER SET latin1 NOT NULL DEFAULT '0',
  "style_name" char(10) NOT NULL,
  PRIMARY KEY ("csrv_ticket_status_id"),
  KEY "code_idx" ("code"),
  KEY "term_idx" ("is_terminal"),
  KEY "init_idx" ("is_initial")
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

CREATE TABLE "csrv_ticket_comment" (
  "csrv_ticket_comment_id" int(11) NOT NULL AUTO_INCREMENT,
  "csrv_ticket_id" int(11) unsigned NOT NULL,
  "created_on" int(11) unsigned NOT NULL DEFAULT '0',
  "author_id" int(11) unsigned NOT NULL DEFAULT '0',
  "message" text CHARACTER SET latin1 NOT NULL,
  "author" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  "updated_on" int(10) unsigned DEFAULT NULL,
  PRIMARY KEY ("csrv_ticket_comment_id"),
  KEY "created_on_idx" ("created_on"),
  KEY "author_idx" ("author_id"),
  KEY "csrv_ticket_idx" ("csrv_ticket_id")
) ENGINE=InnoDB AUTO_INCREMENT=964 DEFAULT CHARSET=utf8;

CREATE TABLE "csrv_ticket_log" (
  "csrv_ticket_log_id" int(11) unsigned NOT NULL AUTO_INCREMENT,
  "created_on" int(10) unsigned DEFAULT NULL,
  "updated_on" int(10) unsigned DEFAULT NULL,
  "csrv_ticket_id" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  "author_id" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  "author" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  "old_value" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  "attr" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  "new_value" varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY ("csrv_ticket_log_id")
) ENGINE=InnoDB AUTO_INCREMENT=9174 DEFAULT CHARSET=utf8;

